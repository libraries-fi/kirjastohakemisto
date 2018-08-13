const POSITION_CACHE_TIME = 1000 * 120;

class GeoLocation {
  static isSupported() {
    return "geolocation" in navigator;
  }

  constructor() {
    if (!GeoLocation.isSupported()) {
      throw new Error("Geolocation is not supported on this platform");
    }

    this.client = navigator.geolocation;
    this.config = new Map;
  }

  get lastLocation() {
    if (!this.config.has("last_location") && history.state.lastLocation) {
      this.config.set("last_location", history.state.lastLocation);
    }
    return this.config.get("last_location");
  }

  get lastUpdate() {
    let location = this.lastLocation;
    return location ? location.timestamp : null;
  }

  get enabled() {
    return this.config.get("enabled");
  }

  async locate(high_accuracy) {
    return this.updateLocation();

    if (Date.now() - POSITION_CACHE_TIME < this.lastUpdate) {
      return Promise.resolve(this.lastLocation);
    } else {
      return this.updateLocation();
    }
  }

  updateLocation() {
    return new Promise((resolve, reject) => {
      let accepted = (pos) => {
        let state = history.state || {};

        Object.assign(state, {
          lastLocation: {
            timestamp: pos.timestamp,
            coords: {
              latitude: pos.coords.latitude,
              longitude: pos.coords.longitude,
            }
          }
        });

        history.replaceState(history.state, "", window.location);

        this.config.set("enabled", true);
        this.config.set("last_location", pos);

        resolve(pos);
      };

      let rejected = (reason) => {
        const PERMISSION_DENIED = 1;
        const POSITION_UNAVAILABLE = 2;
        const TIMEOUT = 3;

        if (reason.code != reason.__proto__.PERMISSION_DENIED) {
          throw new Error("Cannot use location services right now");
        } else {
          this.config.set("enabled", false);
        }
        reject(reason);
      };

      this.client.getCurrentPosition(accepted, rejected, {
        // enableHighAccuracy: high_accuracy,
        maximumAge: POSITION_CACHE_TIME
      });
    });
  }
}

const locator = new GeoLocation;

export { GeoLocation, locator };
