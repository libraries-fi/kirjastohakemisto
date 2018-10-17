import Router from 'vue-router'

import Frontpage from '@/components/frontpage'
import SearchPage from '@/components/searchpage'
import LibraryPage from '@/components/librarypage'
import SlugRedirect from '@/components/slug-redirect'

export default new Router({
  mode: 'history',
  routes: [
    {
      path: '/',
      name: 'front',
      component: Frontpage
    },
    {
      path: '/search',
      name: 'search',
      component: SearchPage
    },
    {
      path: '/:city/:library',
      name: 'library.show',
      component: LibraryPage
    },
    {
      path: '/libraries',
      name: 'library.collection',
    },
    {
      path: '/services',
      name: 'service.collection',
    },
    {
      path: '/info',
      name: 'info'
    },

    {
      path: '/:slug',
      name: 'slug-search',
      component: SlugRedirect
    }
  ]
})
