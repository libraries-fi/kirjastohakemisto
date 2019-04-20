import '@/scss/kirjastohakemisto.scss'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import App from '@/App'
import BootstrapVue from 'bootstrap-vue'
import Vue from 'vue'
import VueI18n from 'vue-i18n'
import VueSession from 'vue-session'
import Router from 'vue-router'
import router from '@/router'
import ToggleButton from 'vue-js-toggle-button'

import { LocationService } from '@/plugins'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import ApiImage from '@/components/api-image'

import fi from 'messages.fi.yaml'
import en from 'messages.en.yaml'
import { detectLanguage } from '@/mixins'

Vue.config.productionTip = false

Vue.use(BootstrapVue)
Vue.use(Router)
Vue.use(ToggleButton)
Vue.use(VueI18n)
Vue.use(VueSession)
Vue.use(LocationService)

Vue.component('fa', FontAwesomeIcon)
Vue.component('api-image', ApiImage)

/**
 * Implements autofocus.
 */
Vue.directive('focus', {
  inserted: (e) => e.focus()
})

function setPageId (id) {
  const identifier = id.replace(/-+/g, '-').replace(/\./g, '--')
  document.body.id = `page--${identifier}`
}

const i18n = new VueI18n({
  locale: detectLanguage(),
  messages: { fi, en }
})

const app = new Vue({
  el: '#app',
  created () {
    setPageId(this.$route.name)
  },
  watch: {
    $route: async (to, from) => {
      setPageId(to.name)
    }
  },
  router,
  i18n,
  template: '<App/>',
  components: { App }
})

// Avoids linter whine
export default app
