/**
 * @deprecated
 */

class GeoLocation {
  static get supported() {
    return "geolocation" in navigator;
  }

  constructor() {
    if (!GeoLocation.supported) {
      throw new Error("Geolocation is not supported on this browser");
    }

    this.client = navigator.geolocation;
    this.config = new Session;
  }

  position() {
    return new Promise((resolve, reject) => {
      let accepted = (pos) => {
        this.config.enabled = true;
        resolve(pos);
      };

      let rejected = (reason) => {
        this.config.set("enabled", reason.code !== 1);
        reject(reason);
      };

      this.config.set("enabled", false);
      this.client.getCurrentPosition(accepted, rejected, {
        enableHighAccuracy: true,
        maximumAge: 1000 * 60 * 10,
      });
    });
  }

  get enabled() {
    return this.config.get("enabled") == true;
  }
}
