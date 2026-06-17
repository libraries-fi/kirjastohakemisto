# Municipal Boundary Generation

## Data source

Municipal boundaries are sourced from the **National Land Survey of Finland** (Maanmittauslaitos) GeoPackage:

[Suomen hallinnolliset kuntajakopohjaiset aluejaot 2026 (1:10 000)](https://www.maanmittauslaitos.fi/kartat-ja-paikkatieto/aineistot-ja-rajapinnat/tuotekuvaukset/hallinnolliset-kuntajakopohjaiset)

The file (`SuomenHallinnollisetKuntajakopohjaisetAluejaot_2026_10k.gpkg`) is **not stored in the repository** due to size. Download it from the link above and place it in `finland-admin-geopackage/`.

## Requirements

- Python 3
- `pyproj` (coordinate reprojection)

```bash
pip3 install --break-system-packages pyproj
```

---

## Generation

The script `backend/bin/update_boundaries.py` reads the GeoPackage, reprojects coordinates from EPSG:3067 to EPSG:4326, simplifies with Douglas-Peucker, and updates the `.GeoJson` files in `osm/`.

### Default command (all municipalities)

```bash
python3 backend/bin/update_boundaries.py \
  --gpkg finland-admin-geopackage/SuomenHallinnollisetKuntajakopohjaisetAluejaot_2026_10k.gpkg \
  --osm-dir osm \
  --tolerance 150
```

### Vaasa special case

Vaasa is a multi-island municipality. `coordinates[0]` only covers the first polygon, dropping the archipelago. Use `--merge-polygons` to combine all shapes:

```bash
python3 backend/bin/update_boundaries.py \
  --gpkg finland-admin-geopackage/SuomenHallinnollisetKuntajakopohjaisetAluejaot_2026_10k.gpkg \
  --osm-dir osm \
  --city Vaasa \
  --tolerance 100 \
  --merge-polygons
```

This regenerates only Vaasa with all shapes merged into one polygon entry, and a finer tolerance for extra detail.

### After generation

```bash
cd backend && php bin/console cache:clear
curl -k https://hakemisto.kirjastot.fi.local/backend/cache-clear
```

## Script options

| Flag | Default | Description |
|---|---|---|
| `--gpkg` | _(required)_ | Path to the GeoPackage file |
| `--osm-dir` | _(required)_ | Path to the `osm/` output directory |
| `--tolerance` | `150` | Douglas-Peucker simplification in meters (`0` = disable) |
| `--city` | _(all)_ | Process only a specific municipality (case-insensitive) |
| `--merge-polygons` | _off_ | Merge all polygons into `coordinates[0]` |
| `--dry-run` | _off_ | Show what would be updated without writing |

## Notes

- The script matches municipalities by `properties.alltags.name` inside each `.GeoJson` file (filenames are irrelevant).
- Unmatched municipalities in the GeoPackage are skipped (existing files left untouched).
- Duplicate files with the same municipality name are automatically cleaned up.
