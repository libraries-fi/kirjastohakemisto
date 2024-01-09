import axios from 'axios'
import { detectLanguage } from '@/mixins'

import config from '@/config'

class Kirkanta {
  constructor (baseurl) {
    this.__url = baseurl
  }

  search (type, params = {}) {
    if (!params.limit) {
      params.limit = 10
    }
    return this.query(type, params).then(response => response.data)
  }

  async get (type, id, params = {}, tryAlsoWithoutLocalization = false) {
    if (typeof id === 'object') {
      params = id
    } else {
      params[parseInt(id) ? 'id' : 'slug'] = id
    }

    params.limit = 1

    let response = await this.query(type, params, tryAlsoWithoutLocalization)

    if (response.data.items.length) {
      return {
        data: response.data.items[0],
        refs: response.data.refs
      }
    } else {
      console.log(params, response)
      throw new Error(`Requested object not found`)
    }
  }

  async query (path, params = {}, tryAlsoWithoutLocalization = false) {
    /**
     * Convert arrays to strings because the API doesn't handle array notation.
     */

    const processed = {}

    for (let key in params) {
      let value = params[key]

      if (typeof value === 'boolean' && !value) {
        // VueBootstrap components might return FALSE when e.g. no checkbox is selected.
        continue
      }

      if (Array.isArray(value)) {
        value = value.length ? value.join(' ') : null
      } else if (typeof value === 'object' && value && 'id' in value) {
        value = value.id
      }

      processed[key] = value
    }

    if (!processed.lang) {
      processed.lang = detectLanguage()
    }

    let result = await axios.get(`${this.__url}/${path}`, { params: processed });
    if (tryAlsoWithoutLocalization && !result.data.items.length) {
      delete processed.lang;
      // Try again without localization.
      result = await axios.get(`${this.__url}/${path}`, { params: processed });
    }
    return result;
  }
}

const kirkanta = new Kirkanta(`${config.apiUrl}/v4`)

export default kirkanta
export { Kirkanta, kirkanta }
