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
