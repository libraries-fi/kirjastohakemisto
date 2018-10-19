import '@/scss/kirjastohakemisto.scss'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import App from './App'
import BootstrapVue from 'bootstrap-vue'
import Vue from 'vue'
import VueI18n from 'vue-i18n'
import Router from 'vue-router'
import router from './router'
import ToggleButton from 'vue-js-toggle-button'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import ApiImage from '@/components/api-image'

Vue.config.productionTip = false

Vue.use(BootstrapVue)
Vue.use(Router)
Vue.use(ToggleButton)
Vue.use(VueI18n)

Vue.component('fa', FontAwesomeIcon)
Vue.component('api-image', ApiImage)

/**
 * Implements autofocus.
 */
Vue.directive('focus', {
  inserted: (e) => e.focus()
})

import fi from 'messages.fi.yaml'
import en from 'messages.en.yaml'

import { detectLanguage } from '@/mixins'

function setPageId(id) {
  document.body.id = "page--" + id.replace(/[-\.]+/, '-')
}

const i18n = new VueI18n({
  locale: detectLanguage(),
  messages: { fi, en },
})

new Vue({
  el: '#app',
  created() {
    setPageId(this.$route.name)
  },
  watch: {
    $route: async (to, from) => {
      setPageId(to.name);
    }
  },
  router,
  i18n,
  template: '<App/>',
  components: { App }
})
