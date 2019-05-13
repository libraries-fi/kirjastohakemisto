<template>
  <b-breadcrumb :items="items" class="mb-0" v-if="items.length > 1"/>
</template>

<script>
import { kirkanta } from '@/mixins'

/**
 * Maps route names to paths.
 */
function buildRouteCache (cache, routes) {
  routes.forEach((route) => {
    cache.set(route.name, route)

    if (route.children) {
      buildRouteCache(cache, route.children)
    }
  })

  return cache
}

export default {
  data: () => ({
    items: [],
    cache: null
  }),
  methods: {
    async buildBreadcrumb (routes, routeMatch) {
      let nameMap = buildRouteCache(new Map(), routes)
      let pathMap = new Map([...nameMap].map(([name, route]) => [route.path, route]))

      let parts = nameMap.get(routeMatch.name).path.split(/\//).filter(p => !!p)
      let crumb = this.items = [{ to: '/', text: this.$t('app.name') }]
      let routeParams = {}

      for (let [i, level] of parts.entries()) {
        let path = '/' + parts.slice(0, i + 1).join('/')
        let route = pathMap.get(path)

        if (!route) {
          // E.g. proxy search pages have no match.
          break
        }

        let routeName = pathMap.get(path).name

        if (level[0] === ':') {
          try {
            let type = level.substr(1)
            let id = routeMatch.params[type]

            if (id) {
              let entity = (await kirkanta.get(type, id)).data
              routeParams[type] = id

              crumb.push({
                text: entity.name || entity.id,
                to: { name: routeName, params: Object.assign({}, routeParams) }
              })
            }
            // level = await kirkanta.get()
          } catch (e) {
            console.error(e)
          }
        } else if (route.meta && route.meta.title) {
          crumb.push({
            text: this.$t(route.meta.title),
            to: { name: routeName, params: Object.assign({}, routeParams) }
          })
        } else if (route.meta && route.meta.titleCallback) {
          // FIXME: Pass some useful arguments
          let titleString = route.meta.titleCallback(path)

          crumb.push({
            text: this.$t(titleString),
            to: { name: routeName, params: Object.assign({}, routeParams) }
          })
        }
      }
    }
  },
  async mounted () {
    this.buildBreadcrumb(this.$router.options.routes, this.$route)
    // this.items = await this.buildBreadcrumb(this.$router.options.routes, this.$route)
  },
  watch: {
    $route (to, from) {
      this.buildBreadcrumb(this.$router.options.routes, to)
    }
  }
}
</script>
