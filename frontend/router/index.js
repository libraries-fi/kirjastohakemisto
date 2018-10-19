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

export default new Router({
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
      component: PageLibraryIndex,
      children: [
        {
          path: '',
          name: 'library.collection',
          meta: {
            indexBy: 'initial'
          },
        },
        {
          path: 'by-municipality',
          name: 'library.collection.by-municipality',
          meta: {
            indexBy: 'municipality'
          },
        },
        {
          path: 'by-consortium',
          name: 'library.collection.by-consortium',
          meta: {
            indexBy: 'consortium'
          },
        }
      ]
    },
    {
      path: '/consortiums',
      name: 'consortium.collection',
      component: PageConsortiumIndex,
    },
    {
      path: '/consortiums/:consortium',
      name: 'consortium.show',
      component: PageConsortium
    },
    {
      path: '/services',
      name: 'service.collection',
      component: PageServiceIndex,
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
      component: RedirectLibrary,
    },
    {
      path: '/c/:slug',
      name: 'rc',
      component: RedirectConsortium,
    },
    {
      path: '/s/:slug',
      name: 'rs',
      component: RedirectService,
    },
    {
      path: '/:city/:library',
      name: 'library.show',
      component: PageLibrary
    },
  ]
})
