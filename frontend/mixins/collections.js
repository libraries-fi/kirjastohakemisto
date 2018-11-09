export function * filtered(items, callback) {
  for (let item of items) {
    if (callback(item)) {
      yield item
    }
  }
}

export function groupBy(items, callback) {
  const groups = new Map

  for (let item of items) {
    let key = callback(item, items)

    if (!groups.has(key)) {
      groups.set(key, [item])
    } else {
      groups.get(key).push(item)
    }
  }

  return groups
}

export function groupByProperty(items, prop, callback) {
  const groups = new Map

  for (let item of items) {
    let key = callback ? callback(item[prop]) : item[prop]

    if (!groups.has(key)) {
      groups.set(key, [item])
    } else {
      groups.get(key).push(item)
    }
  }

  return groups
}

export function toArray(iterable) {
  return [...iterable]
}

/**
 * Takes a map whose values are arrays and appends values into one of them.
 */
export function addToMapArray(map, key, ...values) {
  if (!map.has(key)) {
    map.set(key, values)
  } else {
    map.get(key).push(...values)
  }
  return map
}

/**
 * Add (key, value) to map if key does not exist.
 */
export function addToMap(map, key, value) {
  if (!map.has(key)) {
    map.set(key, value)
  }
}
