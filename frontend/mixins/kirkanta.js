import axios from 'axios'
import { detectLanguage } from '@/mixins'

const KIRKANTA_URL = 'https://api.kirjastot.fi/v4'

class Kirkanta {
  constructor(baseurl) {
    this.__url = baseurl;
  }

  search(type, params = {}) {
    if (!params.limit) {
      params.limit = 10
    }
    return this.query(type, params).then(response => response.data)
  }

  get(type, id, params = {}) {
    if (typeof id == 'object') {
      params = id
    } else {
      params.id = id
    }

    return this.query(type, params).then(response => {
      if (response.data.result.length) {
        return {
          data: response.data.result[0],
          refs: response.data.refs
        }
      } else {
        throw `Requested object not found`
      }
    })
  }

  query(path, params = {}) {
    /**
     * Convert arrays to strings because the API doesn't handle array notation.
     */
    for (let key in params) {
      if (Array.isArray(params[key])) {
        params[key] = params[key].length ? params[key].join(' ') : null
      }
    }

    if (!params.lang) {
      params.lang = detectLanguage()
    }

    return axios.get(`${this.__url}/${path}`, {params})
  }
}

const kirkanta = new Kirkanta(KIRKANTA_URL)

export default kirkanta
export { Kirkanta, kirkanta }
