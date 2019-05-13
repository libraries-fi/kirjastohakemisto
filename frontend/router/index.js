import Router from 'vue-router'

import PageConsortium from '@/components/PageConsortium'
import PageConsortiumIndex from '@/components/PageConsortiumIndex'
import PageFront from '@/components/PageFront'
import PageLibrary from '@/components/PageLibrary'
import PageLibraryIndex from '@/components/PageLibraryIndex'
import PageSearch from '@/components/PageSearch'
import PageService from '@/components/PageService'
import PageServiceIndex from '@/components/PageServiceIndex'
import RedirectLibrary from '@/components/RedirectLibrary'
import RedirectConsortium from '@/components/RedirectConsortium'
import RedirectService from '@/components/RedirectService'

const langcode = document.documentElement.lang

function translateRoutes (routes) {
  console.log('R', langcode, routes)
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

const pathMap = new Map([
  ['search', new Map([
    ['fi', '/haku'],
    ['en', '/search'],
    ['sv', '/sokning']
  ])],
  ['library.collection', new Map([
    ['fi', '/kirjastot'],
    ['en', '/libraries'],
    ['sv', '/biblioteken']
  ])],
  ['library.collection.by-municipality', new Map([
    ['fi', 'kunnittain'],
    ['en', 'by-municipality'],
    ['sv', 'sv--kunnittain--sv']
  ])],
  ['library.collection.by-consortium', new Map([
    ['fi', 'kimpoittain'],
    ['en', 'by-consortium'],
    ['sv', 'sv--kimpoittain--sv']
  ])],
  ['consortium.collection', new Map([
    ['fi', '/kimpat'],
    ['en', '/consortiums'],
    ['sv', '/sv--kimpat--sv']
  ])],
  ['service.collection', new Map([
    ['fi', '/palvelut'],
    ['en', '/services'],
    ['sv', '/tjanster']
  ])],
  ['info', new Map([
    ['fi', '/tietoa'],
    ['en', '/info'],
    ['sv', '/sv--tietoa--sv']
  ])]
])

const routerConfig = {
  mode: 'history',
  routes: [
    {
      path: '/',
      name: 'front',
      component: PageFront
    },
    {
      path: '/search',
      name: 'search',
      component: PageSearch
    },
    {
      path: '/libraries',
      name: 'library.collection',
      component: PageLibraryIndex,
      meta: {
        indexBy: 'initial'
      },
      children: [
        {
          path: 'by-municipality',
          name: 'library.collection.by-municipality',
          meta: {
            indexBy: 'municipality'
          }
        },
        {
          path: 'by-consortium',
          name: 'library.collection.by-consortium',
          meta: {
            indexBy: 'consortium'
          }
        }
      ]
    },
    {
      path: '/consortiums',
      name: 'consortium.collection',
      component: PageConsortiumIndex,
      children: [
        {
          path: ':consortium',
          name: 'consortium.show',
          component: PageConsortium
        }
      ]
    },
    // {
    //   path: '/consortiums/:consortium',
    //   name: 'consortium.show',
    //   component: PageConsortium
    // },
    {
      path: '/services',
      name: 'service.collection',
      component: PageServiceIndex
    },
    {
      path: '/services/:service',
      name: 'service.show',
      component: PageService
    },
    {
      path: '/info',
      name: 'info'
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
      component: PageLibrary
    }
  ]
}

translateRoutes(routerConfig.routes)

export default new Router(routerConfig)
