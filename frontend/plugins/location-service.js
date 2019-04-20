import { geolocation } from '@/mixins'

const CONFIG_KEY = 'location.enabled'

class LocationService {
  constructor (session) {
    this.session = session
  }

  get enabled () {
    return this.session.get(CONFIG_KEY) || false
  }

  async tryEnable () {
    try {
      this.session.set(CONFIG_KEY, true)
      return this.query(true)
    } catch (error) {
      // pass
    }
  }

  async turnOff () {
    this.session.set(CONFIG_KEY, false)
  }

  async query (tryGps = false) {
    await this.isActive()
    let pos = await geolocation.getPosition(tryGps)
    return pos
  }

  async isActive () {
    try {
      await this.isAllowed()

      if (!this.enabled) {
        throw new Error('Location data is disabled in session')
      }

      return true
    } catch (error) {
      this.session.set(CONFIG_KEY, false)
      throw error
    }
  }

  async isAllowed () {
    return geolocation.test()
  }

  static install (Vue, options) {
    if (!('$session' in Vue.prototype)) {
      throw new Error('LocationService must be registered after VueSession')
    }

    Vue.prototype.$location = new LocationService(Vue.prototype.$session)
  }
}

export default LocationService
