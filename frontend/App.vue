<template>
  <div id="app" @click="closeStuff">
    <b-navbar toggleable="md" type="dark" variant="dark" sticky id="nav-main">
      <div class="container">
        <b-navbar-toggle target="nav-collapse"/>
        <b-navbar-brand :to="{ name: 'front' }">{{ $t('app.name') }}</b-navbar-brand>
        <b-collapse id="nav-collapse" is-nav>
          <b-navbar-nav>
            <b-nav-item :to="{ name: 'search' }">{{ $t('nav.search')}}</b-nav-item>
            <b-nav-item :to="{ name: 'library.collection' }">{{ $t('nav.libraries')}}</b-nav-item>
            <b-nav-item :to="{ name: 'consortium.collection' }">{{ $t('nav.consortiums') }}</b-nav-item>
            <b-nav-item :to="{ name: 'service.collection' }">{{ $t('nav.services') }}</b-nav-item>
            <b-nav-item :to="{ name: 'info' }">{{ $t('nav.info') }}</b-nav-item>
          </b-navbar-nav>
        </b-collapse>
        <toggle-button v-model="locationDataAllowed" :sync="true" @change="toggleGeolocation" labels :title="locationPosition"/>
      </div>
    </b-navbar>
    <div class="alert alert-warning mb-0" id="l-alert-box">
      <div class="container alert-container">
        <div class="alert-icon">
          <fa :icon="faExclamationTriangle" size="2x"/>
        </div>
        <div class="alert-messages">
          <p class="m-0">{{ $t('app.beta-warning') }}</p>
          <p v-html="$t('app.beta-link-back')" class="m-0"/>
        </div>
      </div>
    </div>
    <div class="container main-content">
      <router-view></router-view>
    </div>
    <footer id="l-footer">
      <kifi-footer/>
    </footer>
  </div>
</template>

<script>
import { geolocation } from './mixins'
import { faExclamationTriangle } from '@fortawesome/free-solid-svg-icons'

import KifiFooter from '@/components/KifiFooter'

export default {
  name: 'app',
  components: { KifiFooter },
  data: () => ({
    locationDataAllowed: false,
    locationPosition: null,
    faExclamationTriangle
  }),
  created () {
    geolocation.test()
      .then(() => {
        this.locationDataAllowed = true
      })
      .catch(() => {
        this.locationDataAllowed = false
      })
  },
  methods: {
    toggleGeolocation (event) {
      async function run () {
        if (event.value) {
          try {
            let pos = await geolocation.gps()
            this.locationPosition = `${pos.coords.latitude}, ${pos.coords.longitude}`
          } catch (err) {
            this.locationDataAllowed = false
            console.warn('user aborted geolocation')
          }
        }
      }

      run()
    },
    closeStuff (event) {
      if (event.target.tagName !== 'A') {
        this.$root.$emit('bv::hide::popover')
      }
    }
  }
}
</script>

<style lang="scss">
  @import "./scss/bootstrap/init";

  #nav-main {
    .vue-js-switch {
      margin: 0;
    }
  }

  #l-footer {
    background-color: #dedcdc;
    color: black;
  }

  .alert-container {
    display: flex;

    .alert-icon {
      margin-top: 6px;
    }

    .alert-messages {
      flex-grow: 1;
      margin-left: spacing(3);
    }
  }
</style>
