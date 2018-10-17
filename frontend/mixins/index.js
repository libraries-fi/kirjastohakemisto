export * from './kirkanta'
export * from './geolocation'
export * from './language'

function first(items) {
  if (Array.isArray(items)) {
    return items[0]
  }
}

function last(items) {
  if (Array.isArray(items)) {
    return items[items.length - 1]
  }
}

export { first, last }
