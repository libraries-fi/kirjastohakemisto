import axios from 'axios'
import { detectLanguage } from '@/mixins'

import config from '@/config.json'

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

  async get(type, id, params = {}) {
    if (typeof id == 'object') {
      params = id
    } else {
      params.id = id
    }

    let response = await this.query(type, params)

    console.log('R', response.data)

    if (response.data.items.length) {
      return {
        data: response.data.items[0],
        refs: response.data.refs
      }
    } else {
      throw `Requested object not found`
    }
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

const kirkanta = new Kirkanta(`${config.apiUrl}/v4`)

export default kirkanta
export { Kirkanta, kirkanta }
