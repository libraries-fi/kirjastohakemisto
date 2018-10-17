import axios from 'axios'

const IP_LOCATION_URL = '/backend/ip-location'
const LOCATION_CACHE_TIME = 1000 * 120

class GeoLocation {
  constructor() {
    this.__enabled = false

    this.test()
      .then(() => {
        this.__enabled = true
      })
      .catch(() => {
        this.__enabled = false
      })
  }

  get enabled() {
    return this.__enabled
  }

  disable() {
    this.__enabled = false
  }

  async test() {
    return navigator.permissions.query({name: 'geolocation'})
      .then((permission) => {
        if (permission.state == 'granted') {
          return true
        } else {
          throw 'Geolocation is disabled'
        }
      })
  }

  gps() {
    return new Promise((resolve, reject) => {
      const accepted = (pos) => {
        this.__enabled = true
        resolve(pos)
      }

      navigator.geolocation.getCurrentPosition(accepted, reject, {
        maximumAge: LOCATION_CACHE_TIME
      })
    })
  }

  async getPosition(try_gps = false) {
    if (try_gps) {
      return this.gps()
    }

    try {
      await this.test()
      return this.gps()
    } catch (err) {
      return axios.get(IP_LOCATION_URL).then((response) => response.data)
    }
  }
}

const geolocation = new GeoLocation

export default geolocation
export { GeoLocation, geolocation }
