#!/usr/bin/env python3
"""
update_boundaries.py

Reads municipal boundary data from a GeoPackage database and updates existing
GeoJson files in the osm/ directory.

GeoPackage geometry is in EPSG:3067 (ETRS-TM35FIN), reprojected to EPSG:4326
(WGS84 lng/lat) to match existing files. Geometry is simplified (Douglas-Peucker)
in meter-space before reprojection to keep file sizes manageable.

Existing files are matched by properties.alltags.name comparing to namefin.
Only municipalities found in the database with a matching existing file are
updated. Unmatched existing files are left untouched.
"""

import argparse
import json
import math
import os
import struct
import sys

from pyproj import Transformer


# --- Douglas-Peucker simplification -------------------------------------------

def _point_dist_sq(a: tuple, b: tuple) -> float:
    """Squared Euclidean distance between two points."""
    return (a[0] - b[0]) ** 2 + (a[1] - b[1]) ** 2


def _perpendicular_dist_sq(pt: tuple, seg_start: tuple, seg_end: tuple) -> float:
    """Squared perpendicular distance from point to line segment."""
    dx = seg_end[0] - seg_start[0]
    dy = seg_end[1] - seg_start[1]
    seg_len_sq = dx * dx + dy * dy

    if seg_len_sq < 1e-20:
        return _point_dist_sq(pt, seg_start)

    t = max(0.0, min(1.0, ((pt[0] - seg_start[0]) * dx + (pt[1] - seg_start[1]) * dy) / seg_len_sq))
    proj_x = seg_start[0] + t * dx
    proj_y = seg_start[1] + t * dy
    return _point_dist_sq(pt, (proj_x, proj_y))


def _douglas_peucker(points: list, tolerance_sq: float, start: int, end: int) -> list:
    """Recursive Douglas-Peucker simplification. Returns indices to keep."""
    max_dist_sq = 0.0
    max_idx = start

    for i in range(start + 1, end):
        d = _perpendicular_dist_sq(points[i], points[start], points[end])
        if d > max_dist_sq:
            max_dist_sq = d
            max_idx = i

    if max_dist_sq > tolerance_sq:
        left = _douglas_peucker(points, tolerance_sq, start, max_idx)
        right = _douglas_peucker(points, tolerance_sq, max_idx, end)
        return left[:-1] + right  # avoid duplicating max_idx
    else:
        return [start, end]


def simplify_ring(ring: list, tolerance_meters: float) -> list:
    """
    Simplify a ring (list of [x, y] in meters) using Douglas-Peucker.
    A ring is a closed polygon — the last point equals the first.
    Returns simplified ring with at most len(ring) points, at minimum 4.
    """
    n = len(ring)
    if n < 4:
        return ring

    tol_sq = tolerance_meters * tolerance_meters
    # Simplify the open chain (all points except the duplicate closing point).
    indices = _douglas_peucker(ring, tol_sq, 0, n - 1)
    result = [ring[i] for i in indices]

    # Ensure closure.
    if result[-1] != ring[-1]:
        result.append(ring[-1])

    return result


def simplify_multipolygon(coords_3067: list, tolerance_meters: float) -> list:
    """Simplify all rings in a MultiPolygon (EPSG:3067 meter coordinates)."""
    simplified = []
    for polygon in coords_3067:
        rings = []
        for ring in polygon:
            rings.append(simplify_ring(ring, tolerance_meters))
        simplified.append(rings)
    return simplified


# --- WKB / GeoPackage binary parser -------------------------------------------

def parse_gpkg_geometry(
    blob: bytes, transformer: Transformer, tolerance_meters: float = 50,
    merge_polygons: bool = False,
) -> list:
    """
    Parse a GeoPackage geometry blob (EPSG:3067), optionally simplify in
    meter-space, then reproject to EPSG:4326 [lng, lat].
    """
    # Step 1: parse raw EPSG:3067 coordinates (no reprojection).
    coords_3067 = _parse_raw_multipolygon(blob)
    if not coords_3067:
        return []

    # Step 2: simplify in meter-space (skip if tolerance is 0 or negative).
    if tolerance_meters > 0:
        coords_3067 = simplify_multipolygon(coords_3067, tolerance_meters)

    # Step 3: reproject from EPSG:3067 → EPSG:4326.
    coords_4326 = []
    for polygon in coords_3067:
        rings_4326 = []
        for ring in polygon:
            ring_4326 = []
            for x, y in ring:
                lng, lat = transformer.transform(x, y)
                if math.isnan(lng) or math.isnan(lat) or math.isinf(lng) or math.isinf(lat):
                    print(f"  WARNING: NaN/Inf coordinate from ({x}, {y})", file=sys.stderr)
                    continue
                ring_4326.append([lng, lat])
            rings_4326.append(ring_4326)
        coords_4326.append(rings_4326)

    # Step 4: sort polygons by total vertex count (descending) so the
    # largest/main boundary is always first — compatible with PHP code
    # that only reads coordinates[0].
    coords_4326.sort(key=lambda p: sum(len(r) for r in p), reverse=True)

    # Drop degenerate single-point polygons.
    coords_4326 = [p for p in coords_4326 if sum(len(r) for r in p) > 1]

    # Step 5: optionally merge all polygons into one — puts all rings into
    # coordinates[0] so PHP code that reads only the first polygon still
    # sees all shapes. Note: this breaks GeoJSON interior/exterior ring
    # semantics (extra rings may render as holes instead of filled areas).
    if merge_polygons and len(coords_4326) > 1:
        merged_rings = []
        for polygon in coords_4326:
            merged_rings.extend(polygon)
        coords_4326 = [merged_rings]

    return coords_4326


def _parse_raw_multipolygon(blob: bytes) -> list:
    """Parse GPKG blob → raw MultiPolygon [[[rings]]] in EPSG:3067 [x, y]."""
    offset = 0

    if len(blob) < 8:
        raise ValueError("Geometry blob too short for GP header")
    magic = blob[offset:offset + 2]
    if magic != b"GP":
        raise ValueError(f"Invalid GP magic: {magic}")
    offset += 2
    # version = blob[offset]
    offset += 1
    flags = blob[offset]
    offset += 1
    # srs_id = struct.unpack("<I", blob[offset:offset + 4])[0]
    offset += 4
    extended = bool(flags & 0x01)

    if extended:
        offset += 32  # skip envelope

    endian, offset = _read_byte_order(blob, offset)
    geom_type, offset = _read_uint32(blob, offset, endian)

    if geom_type == 6:  # MultiPolygon
        return _parse_raw_multipolygon_body(blob, offset, endian)
    elif geom_type == 3:  # Polygon
        coords = _parse_raw_polygon_body(blob, offset, endian)
        return [coords]
    else:
        raise ValueError(f"Unsupported geometry type: {geom_type}")


def _read_byte_order(blob: bytes, offset: int):
    bo = blob[offset]
    return ("<" if bo == 1 else ">"), offset + 1


def _read_uint32(blob: bytes, offset: int, endian: str):
    return struct.unpack(endian + "I", blob[offset:offset + 4])[0], offset + 4


def _read_raw_point(blob: bytes, offset: int, endian: str):
    """Read a raw (x, y) pair in EPSG:3067."""
    x, y = struct.unpack(endian + "dd", blob[offset:offset + 16])
    return (x, y), offset + 16


def _parse_raw_ring(blob: bytes, offset: int, endian: str):
    num_points, offset = _read_uint32(blob, offset, endian)
    points = []
    for _ in range(num_points):
        pt, offset = _read_raw_point(blob, offset, endian)
        points.append(pt)
    return points, offset


def _parse_raw_polygon_body(blob: bytes, offset: int, endian: str):
    endian, offset = _read_byte_order(blob, offset)
    poly_type, offset = _read_uint32(blob, offset, endian)
    if poly_type != 3:
        raise ValueError(f"Expected Polygon type 3, got {poly_type}")

    num_rings, offset = _read_uint32(blob, offset, endian)
    rings = []
    for _ in range(num_rings):
        ring, offset = _parse_raw_ring(blob, offset, endian)
        rings.append(ring)
    return rings, offset


def _parse_raw_multipolygon_body(blob: bytes, offset: int, endian: str):
    num_polygons, offset = _read_uint32(blob, offset, endian)
    polygons = []
    for _ in range(num_polygons):
        rings, offset = _parse_raw_polygon_body(blob, offset, endian)
        polygons.append(rings)
    return polygons


# --- File matching and updating -----------------------------------------------

def find_existing_by_name(osm_dir: str, namefin: str) -> tuple[dict, str] | None:
    """
    Search osm/ for a GeoJson file whose properties.alltags.name matches
    namefin (case-insensitive). Returns (parsed JSON data, filename) or None.
    """
    namefin_lower = namefin.strip().lower()
    for filename in os.listdir(osm_dir):
        if not filename.endswith(".GeoJson"):
            continue
        path = os.path.join(osm_dir, filename)
        try:
            with open(path, "r", encoding="utf-8") as f:
                data = json.load(f)
            file_name = data.get("properties", {}).get("alltags", {}).get("name", "")
            if file_name.strip().lower() == namefin_lower:
                return data, filename
        except (json.JSONDecodeError, KeyError, UnicodeDecodeError):
            continue
    return None


def update_geojson(
    existing: dict,
    namefin: str,
    nameswe: str,
    natcode: str,
    coordinates: list,
) -> dict:
    """
    Preserve existing metadata, update names and geometry from database.
    """
    # Preserve top-level structure
    if "properties" not in existing:
        existing["properties"] = {}
    if "alltags" not in existing["properties"]:
        existing["properties"]["alltags"] = {}

    props = existing["properties"]
    tags = props["alltags"]

    # Update names
    props["name"] = namefin
    props["localname"] = namefin
    tags["name"] = namefin
    tags["name:fi"] = namefin
    tags["name:sv"] = nameswe if nameswe else namefin
    tags["name:en"] = namefin  # fallback: Finnish for English

    # Update municipality code
    tags["ref"] = natcode

    # Update geometry
    existing["geometry"]["type"] = "MultiPolygon"
    existing["geometry"]["coordinates"] = coordinates

    return existing


# --- Main ---------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(
        description="Update municipal boundary GeoJson files from a GeoPackage database"
    )
    parser.add_argument(
        "--gpkg",
        required=True,
        help="Path to the GeoPackage .gpkg file",
    )
    parser.add_argument(
        "--osm-dir",
        required=True,
        help="Path to the osm/ directory containing .GeoJson files",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Show what would be updated without writing files",
    )
    parser.add_argument(
        "--tolerance",
        type=float,
        default=50.0,
        help="Douglas-Peucker simplification tolerance in meters (default: 50, use 0 to disable)",
    )
    parser.add_argument(
        "--city",
        type=str,
        default=None,
        help="Only process this specific municipality (case-insensitive match on namefin)",
    )
    parser.add_argument(
        "--merge-polygons",
        action="store_true",
        help="Merge all polygons into one so coordinates[0] contains all shapes (for complex multi-island municipalities like Vaasa)",
    )
    args = parser.parse_args()

    if not os.path.isdir(args.osm_dir):
        print(f"ERROR: osm directory not found: {args.osm_dir}", file=sys.stderr)
        sys.exit(1)

    # Open GeoPackage
    try:
        import sqlite3
        db = sqlite3.connect(args.gpkg)
        cur = db.cursor()
    except Exception as e:
        print(f"ERROR: Cannot open GeoPackage: {e}", file=sys.stderr)
        sys.exit(1)

    # Read all Kunta rows
    cur.execute(
        "SELECT id, namefin, nameswe, natcode, multipolygon FROM Kunta ORDER BY namefin"
    )
    rows = cur.fetchall()
    print(f"Found {len(rows)} municipalities in GeoPackage.")
    print(f"Simplification tolerance: {args.tolerance} m")

    # Coordinate transformer
    transformer = Transformer.from_crs("EPSG:3067", "EPSG:4326", always_xy=True)

    updated = 0
    skipped_no_match = 0
    skipped_no_geom = 0
    total_before = 0
    total_after = 0

    for row in rows:
        db_id, namefin, nameswe, natcode, geom_blob = row

        # Filter by city if specified
        if args.city and namefin.strip().lower() != args.city.strip().lower():
            continue

        if not geom_blob:
            skipped_no_geom += 1
            continue

        # Find matching existing file
        match = find_existing_by_name(args.osm_dir, namefin)
        if match is None:
            skipped_no_match += 1
            continue
        existing, existing_filename = match

        # Parse geometry (with simplification)
        try:
            coordinates = parse_gpkg_geometry(geom_blob, transformer, args.tolerance, args.merge_polygons)
        except Exception as e:
            print(f"  WARNING: {namefin}: geometry parse error: {e}", file=sys.stderr)
            continue

        # Build updated GeoJSON
        updated_data = update_geojson(existing, namefin, nameswe, natcode, coordinates)

        # Count vertices for summary
        pts = sum(len(ring) for poly in coordinates for ring in poly)
        total_after += pts

        # Preserve original filename
        out_path = os.path.join(args.osm_dir, existing_filename)

        if args.dry_run:
            print(f"  [DRY RUN] Would update: {namefin} ({natcode}) → {existing_filename}")
        else:
            with open(out_path, "w", encoding="utf-8") as f:
                json.dump(updated_data, f, ensure_ascii=False, separators=(",", ":"))
            print(f"  Updated: {namefin} ({natcode}) → {existing_filename}")

            # Remove other files with the same alltags.name (stale duplicates)
            _cleanup_duplicates(args.osm_dir, namefin, existing_filename)

        updated += 1

    db.close()

    print()
    print(f"Summary: {updated} updated, {skipped_no_match} no matching file, "
          f"{skipped_no_geom} no geometry")
    if total_after > 0:
        print(f"Total vertices after simplification: {total_after:,}")


def _cleanup_duplicates(osm_dir: str, namefin: str, keep_filename: str):
    """Remove any other GeoJson files with the same alltags.name."""
    namefin_lower = namefin.strip().lower()
    for fn in os.listdir(osm_dir):
        if fn == keep_filename or not fn.endswith(".GeoJson"):
            continue
        path = os.path.join(osm_dir, fn)
        try:
            with open(path, "r", encoding="utf-8") as f:
                data = json.load(f)
            file_name = data.get("properties", {}).get("alltags", {}).get("name", "")
            if file_name.strip().lower() == namefin_lower:
                os.remove(path)
                print(f"    Cleaned up duplicate: {fn}")
        except (json.JSONDecodeError, KeyError, UnicodeDecodeError, OSError):
            pass


if __name__ == "__main__":
    main()
