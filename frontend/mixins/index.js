import * as constants from './constants'

export * from './kirkanta'
export * from './geolocation'
export * from './language'
export * from './collections'

export { constants }

export function first (items) {
  if (Array.isArray(items)) {
    return items[0]
  }
}

export function last (items) {
  if (Array.isArray(items)) {
    return items[items.length - 1]
  }
}

export function initial (stringValue) {
  return (`${stringValue}`).substring(0, 1).toUpperCase()
}

export function coordStr (coordsObject) {
  return `${coordsObject.latitude},${coordsObject.longitude}`
}
