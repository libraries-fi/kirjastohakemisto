<template>
  <div id="app" @click="closeStuff">
    <div id="skip-links">
      <a href="#maincontent" class="sr-only sr-only-focusable skip-link text-white bg-primary p-3 position-absolute">{{ $t('app.skip-link')}}</a>
    </div>
    <b-navbar toggleable="md" type="light" variant="light" sticky id="nav-main">
      <div class="container py-1">
        <b-navbar-toggle target="nav-collapse"/>
        <b-navbar-brand :to="{ name: 'front' }" :class="'text-uppercase font-weight-bold h1 mb-0'">{{ $t('app.name') }}</b-navbar-brand>
        <b-collapse id="nav-collapse" is-nav>
          <b-navbar-nav>
            <b-nav-item :to="{ name: 'search' }">{{ $t('nav.search')}}</b-nav-item>
            <b-nav-item :to="{ name: 'library.collection' }">{{ $t('nav.libraries')}}</b-nav-item>
            <b-nav-item :to="{ name: 'consortium.collection' }">{{ $t('nav.consortiums') }}</b-nav-item>
            <b-nav-item :to="{ name: 'service.collection' }">{{ $t('nav.services') }}</b-nav-item>
            <li v-if="isFinnish()" class="nav-item ml-lg-4">
              <a href="https://registret.biblioteken.fi/" class="nav-link">Svenska</a>
            <li v-else class="nav-item ml-lg-4">
              <a href="https://hakemisto.kirjastot.fi/haku" class="nav-link">Suomeksi</a>
            </li>
            <li v-if="isEnglish()" class="nav-item">
              <a href="https://registret.biblioteken.fi/" class="nav-link">Svenska</a>
            <li v-else class="nav-item">
              <a href="https://directory.libraries.fi/" class="nav-link">English</a>
            </li>
          </b-navbar-nav>
        </b-collapse>
        <div class="text-center">
          <div class="location-control text-uppercase">{{ $t('app.location') }}</div>
          <toggle-button
                 :labels="locationToggleLabels"
                 :color="{checked: '#358535', unchecked: '#747777', disabled: '#CCCCCC'}"
                 :width="62"
                 :title="locationPosition"
                 :value="locationDataEnabled"
                 :sync="true"
                 @change="toggleGeolocation"
          />
        </div>
      </div>
    </b-navbar>
    <div id="maincontent" class="container main-content pt-3 pb-5">
      <breadcrumb/>
      <router-view :key="locationDataEnabled"></router-view>
    </div>
    <footer id="l-footer" class="bg-light">
      <kifi-footer/>
      <div class="footer-logos py-2 pl-3">
        <p v-if="isFinnish()" class="text-center text-white text-uppercase mt-2">
          Sivusto on osa 
          <a href="https://www.kirjastot.fi" target="_top" class="text-white">Kirjastot.fi-kokonaisuutta<span class="sr-only"> (ulkoinen linkki)</span></a>
        </p>
        <p v-if="isSwedish()" class="text-center text-white text-uppercase mt-2">
          Webbplatsen är en del av 
          <a href="https://www.biblioteken.fi" target="_top" class="text-white">Biblioteken.fi-helheten<span class="sr-only"> (extern länk)</span></a>
        </p>
        <p v-if="isEnglish()" class="text-center text-white text-uppercase mt-2">
          This website is part of the 
          <a href="https://www.libraries.fi" target="_top" class="text-white">Libraries.fi entity<span class="sr-only"> (external link)</span></a>
        </p>
        <div class="container d-md-flex justify-content-between text-center text-md-left py-3">
          <span v-if="isFinnish()"><LogoKirjastotFi/></span>
          <span v-if="isSwedish()"><LogoBibliotekenFi/></span>
          <span v-if="isEnglish()"><LogoLibrariesFi/></span>
          <div class="pt-4 pt-md-0">
            <LogoCommonLibrary/>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { faExclamationTriangle } from '@fortawesome/free-solid-svg-icons'
import { detectLanguage } from '@/mixins'

import Breadcrumb from '@/components/Breadcrumb'
import KifiFooter from '@/components/KifiFooter'
import LogoKirjastotFi from '@/components/LogoKirjastotFi'
import LogoBibliotekenFi from '@/components/LogoBibliotekenFi'
import LogoLibrariesFi from '@/components/LogoLibrariesFi'
import LogoCommonLibrary from '@/components/LogoCommonLibrary'

const langcode = detectLanguage()

export default {
  name: 'app',
  components: { Breadcrumb, KifiFooter, LogoKirjastotFi, LogoBibliotekenFi, LogoLibrariesFi, LogoCommonLibrary },
  data: () => ({
    locationPosition: null,
    locationDataEnabled: false,
    faExclamationTriangle
  }),
  computed: {
    locationToggleLabels()
    {
      if (this.isFinnish()) {
        return {checked: 'Päällä', unchecked: 'Pois'};
      } else if (this.isSwedish()) {
        return {checked: 'På', unchecked: 'Av'};
      } else if (this.isEnglish()) {
        return {checked: 'On', unchecked: 'Off'};
      }
    }
  },
  methods: {
    isFinnish () {
      if (langcode === 'fi') {
        return true
      } 
      return false
    },
    isEnglish () {
      if (langcode === 'en') {
        return true
      } 
      return false
    },
    isSwedish () {
      if (langcode === 'sv') {
        return true
      } 
      return false
    },
    toggleGeolocation (event) {
      if (event.value) {
        this.$location.tryEnable()
        .then(pos => {
          this.locationPosition = `${pos.coords.latitude}, ${pos.coords.longitude}`
          this.locationDataEnabled = true
        })
        .catch(error => {
          console.error(error)
          this.locationDataEnabled = false
        });
      } else {
        this.$location.turnOff()
        this.locationDataEnabled = false
      }
    },
    closeStuff (event) {
      if (event.target.tagName !== 'A') {
        this.$root.$emit('bv::hide::popover')
      }
    },
    async updatePageTitle () {
      let titleElement = document.querySelector('title')
      let titleSuffix = titleElement.innerText.substr(titleElement.innerText.indexOf(' –'))
      let route = this.$route
      let meta = route.meta

      if (meta) {
        if (meta.title) {
          titleElement.innerText = this.$t(meta.title)
        } else if (meta.titleCallback) {
          let titleText = await meta.titleCallback({ route, $t: this.$t })
          titleElement.innerText = titleText
        }
      }

      titleElement.innerText += titleSuffix
    }
  },
  mounted () {
    this.updatePageTitle()
    if (this.$location.enabled){
      // Make sure we have actually location enabled.
      this.$location.isActive()
      .then(() => {
        this.locationDataEnabled = true;
      })
      .catch(() => {
        this.locationDataEnabled = false;
      });
    }
  },
  watch: {
    async $route (to, from) {
      this.updatePageTitle()
    }
  }
}
</script>

<style lang="scss">
  @import "./scss/bootstrap/init";

  .skip-link {
    top: 0;
    left: 0;
    z-index: 2500;
  }

  #nav-main {
    .location-control {
      font-size: 70%;
    }

    .vue-js-switch {
      margin: 0;
    }
  }

  #l-footer {
    .footer-logos {
      background-color: #25284d;

      @include media-breakpoint-down("sm") {
        .public-library-logo {
          width: 100%;
        }
      }

      p {
        font-size: .7rem;
        letter-spacing: .025rem;

        a {
          border-bottom: 1px dashed white;

          &:hover {
            text-decoration: none;
          }

          &:after {
            content: " " url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNS4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAgLS0+DQo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiIFsNCgk8IUVOVElUWSBuc19mbG93cyAiaHR0cDovL25zLmFkb2JlLmNvbS9GbG93cy8xLjAvIj4NCl0+DQo8c3ZnIHZlcnNpb249IjEuMSINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sbnM6YT0iaHR0cDovL25zLmFkb2JlLmNvbS9BZG9iZVNWR1ZpZXdlckV4dGVuc2lvbnMvMy4wLyINCgkgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMHB4IiBoZWlnaHQ9IjEycHgiIHZpZXdCb3g9IjAgMCAxMCAxMiIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTAgMTIiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGRlZnM+DQo8L2RlZnM+DQo8cG9seWdvbiBmaWxsPSIjRkZGRkZGIiBwb2ludHM9IjQuOTk1LDUuODk0IDcuNzU5LDMuMTI5IDguOTYzLDQuMzMzIDEwLjEyNSwwIDUuNzksMS4xNiA2Ljk5MywyLjM2MyA0LjIyOSw1LjEyOCAiLz4NCjxwb2x5Z29uIGZpbGw9IiNGRkZGRkYiIHBvaW50cz0iNyw1LjEyOSA3LDkgMSw5IDEsMyA0Ljk5NywzIDMuOTk2LDIgMCwyIDAsMTAgOCwxMCA4LDYuMTI5ICIvPg0KPC9zdmc+DQo=");
          }
        }
      }
    }
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
