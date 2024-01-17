import Router from 'vue-router'

import PageAccessibilityStatement from '@/components/PageAccessibilityStatement'
import PageConsortium from '@/components/PageConsortium'
import PageConsortiumIndex from '@/components/PageConsortiumIndex'
import PageFront from '@/components/PageFront'
import PageInfo from '@/components/PageInfo'
import PageOpenData from '@/components/PageOpenData'
import PageWidgetBuilder from '@/components/PageWidgetBuilder'
import PageLibrary from '@/components/PageLibrary'
import PageLibraryIndex from '@/components/PageLibraryIndex'
import PageSearch from '@/components/PageSearch'
import PageService from '@/components/PageService'
import PageServiceIndex from '@/components/PageServiceIndex'
import RedirectLibrary from '@/components/RedirectLibrary'
import RedirectConsortium from '@/components/RedirectConsortium'
import RedirectService from '@/components/RedirectService'
import EmptyRouterPage from '@/components/EmptyRouterPage'

import { detectLanguage, kirkanta } from '@/mixins'

const langcode = detectLanguage()

function translateRoutes (routes) {
  routes.forEach((route) => {
    if (route.path && route.name) {
      let translations = pathMap.get(route.name)

      if (translations) {
        route.path = translations.get(langcode)
      }
    }

    if (route.children) {
      translateRoutes(route.children)
    }
  })

  return routes
}

/**
 * Maps route names to translated paths.
 */
const pathMap = new Map([
  ['search', new Map([
    ['fi', '/haku'],
    ['en', '/search'],
    ['sv', '/sokning']
  ])],
  ['library.collection', new Map([
    ['fi', '/kirjastot'],
    ['en', '/libraries'],
    ['sv', '/bibliotek']
  ])],
  ['library.collection.by-municipality', new Map([
    ['fi', 'kunnittain'],
    ['en', 'by-municipality'],
    ['sv', 'enligt-kommun']
  ])],
  ['library.collection.by-consortium', new Map([
    ['fi', 'kimpoittain'],
    ['en', 'by-consortium'],
    ['sv', 'enligt-natverk']
  ])],
  ['consortium.collection', new Map([
    ['fi', '/kimpat'],
    ['en', '/consortiums'],
    ['sv', '/natverk']
  ])],
  ['consortium.show', new Map([
    ['fi', '/kimpat/:consortium'],
    ['en', '/consortiums/:consortium'],
    ['sv', '/natverk/:consortium']
  ])],
  ['service.collection', new Map([
    ['fi', '/palvelut'],
    ['en', '/services'],
    ['sv', '/tjanster']
  ])],
  ['service.show', new Map([
    ['fi', '/palvelut/:service'],
    ['en', '/services/:service'],
    ['sv', '/tjanster/:service']
  ])],
  ['accessibility', new Map([
    ['fi', '/saavutettavuusseloste'],
    ['en', '/accessibility-statement'],
    ['sv', '/tillganglighetsutlatande']
  ])],
  ['info', new Map([
    ['fi', '/tietoa-palvelusta'],
    ['en', '/info'],
    ['sv', '/info-om-tjansten']
  ])]
])

/**
 * Standard Vue router configuration.
 *
 * NOTE: Paths are here too for fallback / consistency purposes. Also because translations were
 * added after setting up the initial configuration so I did not bother removing them. Might be
 * a potential source of bugs or confusion.
 */
export const routerConfig = {
  mode: 'history',
  routes: [
    {
      path: '/',
      name: 'front',
      component: PageFront,
      meta: {
        title: 'app.name'
      }
    },
    {
      path: '/search',
      name: 'search',
      component: PageSearch,
      meta: {
        title: 'search.placeholder'
      }
    },
    {
      path: '/libraries',
      name: 'library.collection',
      component: PageLibraryIndex,
      meta: {
        indexBy: 'initial',
        title: 'nav.libraries'
      },
      children: [
        {
          path: 'by-municipality',
          name: 'library.collection.by-municipality',
          meta: {
            indexBy: 'municipality',
            title: 'nav.libraries'
          }
        },
        {
          path: 'by-consortium',
          name: 'library.collection.by-consortium',
          meta: {
            indexBy: 'consortium',
            title: 'nav.libraries'
          }
        }
      ]
    },
    {
      path: '/consortiums',
      component: EmptyRouterPage,
      children: [
        {
          path: '/consortiums',
          name: 'consortium.collection',
          component: PageConsortiumIndex,
          meta: {
            title: 'nav.consortiums'
          }
        },
        {
          path: '/consortiums/:consortium',
          name: 'consortium.show',
          component: PageConsortium,
          meta: {
            async titleCallback (context) {
              return (await kirkanta.get('consortium', context.route.params.consortium, {}, true)).data.name
            }
          }
        }
      ]
    },
    {
      path: '/services',
      component: EmptyRouterPage,
      children: [
        {
          path: '/services',
          name: 'service.collection',
          component: PageServiceIndex,
          meta: {
            title: 'nav.services'
          }
        },
        {
          path: '/services/:service',
          name: 'service.show',
          component: PageService,
          meta: {
            async titleCallback (context) {
              return (await kirkanta.get('service', context.route.params.service)).data.name
            }
          }
        }
      ]
    },
    {
      path: '/accessibility-statement',
      name: 'accessibility',
      component: PageAccessibilityStatement,
      meta: {
        title: 'nav.accessibility-statement'
      }
    },
    {
      path: '/info',
      name: 'info',
      component: PageInfo,
      meta: {
        title: 'nav.info'
      }
    },
    {
      path: '/info/widgets',
      name: 'info-widget',
      component: PageWidgetBuilder,
      meta: {
        title: 'nav.widget-builder'
      }
    },
    {
      path: '/open-data',
      name: 'open-data',
      component: PageOpenData,
      meta: {
        title: 'nav.open-data'
      }
    },
    {
      path: '/:slug',
      name: 're',
      component: RedirectLibrary
    },
    {
      path: '/c/:slug',
      name: 'rc',
      component: RedirectConsortium
    },
    {
      path: '/s/:slug',
      name: 'rs',
      component: RedirectService
    },
    {
      path: '/:city/:library',
      name: 'library.show',
      component: PageLibrary,
      meta: {
        async titleCallback (context) {
          return (await kirkanta.get('library', context.route.params.library, {}, true)).data.name
        }
      }
    }
  ]
}

translateRoutes(routerConfig.routes)

export default new Router(routerConfig)
