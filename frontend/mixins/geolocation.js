import axios from 'axios'
import { detectLanguage } from './language'
import { demo } from '@/config'

const IP_LOCATION_URL = '/backend/ip-location'
const LOCATION_CACHE_TIME = 1000 * 120
const DEMO_MODE_ENABLED = (demo && demo.enabled && demo.position)

export class GeoLocation {
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

  /**
   * Tests whether location services have been allowed by the user.
   */
  async test() {
    if (DEMO_MODE_ENABLED) {
      return true
    }

    return navigator.permissions.query({name: 'geolocation'})
      .then((permission) => {
        if (permission.state == 'granted') {
          return true
        } else {
          throw 'Geolocation is disabled'
        }
      })
  }

  /**
   * Query client location services.
   */
  gps() {
    if (DEMO_MODE_ENABLED) {
      return demo.position
    }

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

  ip() {
    return axios.get(IP_LOCATION_URL).then((response) => response.data)
  }

  /**
   * Utility function that calls test() and then gps()
   */
  async tryGps() {
    await this.test()
    return this.gps()
  }

  /**
   *
   */
  async getPosition(try_gps = false) {
    if (try_gps) {
      return this.gps()
    }

    try {
      await this.test()
      return this.gps()
    } catch (err) {
      return this.ip()
    }
  }
}

const distanceFormatter = new Intl.NumberFormat(detectLanguage(), {
  maximumFractionDigits: 1
})

export function formatDistance(distance) {
  if (distance < 1000) {
    let meters = Math.ceil(distance)
    return `${meters} m`
  } else {
    let kmeters = distanceFormatter.format(distance / 1000)
    return `${kmeters} km`
  }
}

export const geolocation = new GeoLocation;
