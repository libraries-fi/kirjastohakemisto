
import Map from "ol/map";
import View from "ol/view";
import TileLayer from "ol/layer/tile";
import XYZ from "ol/source/xyz";
import OSM from "ol/source/osm";
import proj from "ol/proj";

import FullScreen from "ol/control/fullscreen";
import Zoom from "ol/control/zoom";
import Attribution from "ol/control/attribution";

import Feature from "ol/feature";
import Point from "ol/geom/point";

import Style from "ol/style/style";
import Icon from "ol/style/icon";
import { default as SVector } from "ol/source/vector";
import { default as LVector } from "ol/layer/vector";

import extent from "ol/extent";
import interaction from "ol/interaction";

const DEFAULT_ZOOM = 15;

/*
 * OpenLayers servers don't cache tiles beyond zoom level 17 and they recommend that
 * we avoid overloading the services with pointless zooming. It can also get very slow
 * after this, so it is better to limit the user's ability to zoom to a sensible value.
 */
const MAX_ZOOM = 18;

const MapView = (($) => {
  function transform_coordinates(coords) {
    var x = Math.round(coords[0] * 10000) / 10000;
    var y = Math.round(coords[1] * 10000) / 10000;

    if (x && y) {
      return proj.transform([x, y], "EPSG:4326", "EPSG:3857");
    } else {
      return null;
    }
  }

  class MapView {
    static openMap(config) {
      let map_id = Math.ceil(Math.random() * 9000 + 999);

      let element = document.createElement("div");
      element.className = "map-view map-popup";
      element.id = "map-view-" + map_id;

      let container = document.createElement("div");
      container.className = "map-hide-container";
      container.id = "map-hide-container-" + map_id;

      container.appendChild(element);
      document.body.appendChild(container);

      let map = new MapView(element, config);
      map.show();

      return map;
    }

    constructor(element, config) {
      this._element = element;
      this._config = config;
    }

    get initialPosition() {
      return this._config.pos || [];
    }

    show() {
      let controls = this._config.controls || [new Zoom, new FullScreen, new Attribution];

      let map = new Map({
        target: this._element,
        controls: controls,
        layers: [
          new TileLayer({
            // source: new XYZ({
            //   // url:
            // })

            source: new OSM({ layer: "sat" })
          })
        ],
        view: new View({
          center: transform_coordinates(this.initialPosition),
          zoom: Math.min(DEFAULT_ZOOM, MAX_ZOOM),
          maxZoom: MAX_ZOOM,
        })
      });

      if (this._config.fullscreen) {
        $(this._element).find(".ol-full-screen-false").trigger("click");
      }

      this._map = map;

      let pins = [{coords: this.initialPosition}];

      if (this._config.userLocation) {
        pins.push({coords: this._config.userLocation, role: "user"});
      }

      this.addPins(pins);
    }

    addPins(points, options) {
      let positions = new Array(points.length);

      let pins = points.map((point, i) => {
        let role = point.role || "library";
        let pos = transform_coordinates(point.coords);

        let pin = new Feature({
          geometry: new Point(pos),
          name: role
        });

        positions[i] = pos;

        switch (role) {
          case "library":
            pin.setStyle(new Style({
              image: new Icon({
                anchor: [24, 48],
                anchorXUnits: "pixels",
                anchorYUnits: "pixels",
                src: "/images/icons/map-marker-2-48.png"
              })
            }));
            break;

          case "user":
            pin.setStyle(new Style({
              image: new Icon({
                anchor: [16, 32],
                anchorXUnits: "pixels",
                anchorYUnits: "pixels",
                src: "/images/marker-circle-small.png"
              })
            }));
            break;
        }

        return pin;
      });

      let source = new SVector({features: pins});
      let layer = new LVector({source: source});

      this._map.addLayer(layer);

      if (points.length > 1) {
        let bbox = extent.boundingExtent(positions);
        this._map.getView().fit(bbox);
      }
    }
  }

  return MapView;
})(jQuery);

export { MapView };
