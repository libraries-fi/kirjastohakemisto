<template>
  <main v-if="library" class="pt-3">
    <div v-b-visible="visibleHandler" class="d-block d-md-none" v-bind:style="{ 'height': '1px', 'background-color': 'transparent', 'position': 'fixed' }"></div>
    <div class="">
      <h1>
        {{ library.name }}
        <b-badge v-if="library.distance" variant="light" class="float-right">{{ formatDistance(library.distance) }}</b-badge>
      </h1>
      <div class="d-block d-md-flex justify-content-between my-3">
        <div class="col-md-6 col-xl-7 mt-md-3 mb-md-0">
          <blockquote v-if="library.slogan">
            <fa :icon="faQuoteRight" aria-hidden="true"/>
            {{ library.slogan }}
          </blockquote>
        </div>
        <div v-if="consortium" class="col-md-6 col-xl-5 text-center mt-md-n2 consortium">
          <h3 class="h5 text-uppercase mb-md-1">
            {{ $t('library.consortium') }}: <span class="font-family-base font-weight-normal">{{ consortium.name }}</span>
          </h3>
          <p class="mb-md-0">
            <a :href="consortium.homepage" class="d-block h4 font-family-base font-weight-bolder external-link mb-1">{{ $t('library.web-library') }}</a>
            <small>{{ $t('library.web-library-tip') }}.</small>
          </p>
        </div>
      </div>
    </div>

    <div>
      <div class="row">

        <!-- Main column -->
        <div class="col-md-6 col-xl-7">

          <b-tabs class="tabs-library-content mb-5" @input="onChangeTab">
            <b-tab :active="selected_tab_name === 'tab_presentation'">
              <template #title>{{ $t('library.tab-presentation') }}</template>
              <h2 class="mt-4">{{ $t('library.tab-presentation') }}</h2>
              <section v-if="library.pictures.length" class="cover-photo-frame mb-3">
                <h2 class="sr-only">{{ $t('library.photos') }}</h2>
                <photos :source="library.pictures"/>
              </section>
              <section v-if="library.description" class="info-links">
                <div v-if="library.description" v-html="library.description"/>
              </section>
              <section v-if="hasBuildingInfo()" class="info-links">
                <p class="bg-light p-3">
                <span v-if="library.founded" class="mr-3">
                  <strong>{{ $t('library.established-year') }}:</strong> {{ library.founded }}
                </span>
                <span v-if="library.buildingInfo.buildingName" class="mr-3">
                  <strong>{{ $t('library.building') }}:</strong> {{ library.buildingInfo.buildingName }}
                </span>
                <span v-if="library.buildingInfo.constructionYear" class="mr-3">
                  <strong>{{ $t('library.built') }}:</strong> {{ library.buildingInfo.constructionYear }}
                </span>
                <span v-if="library.buildingInfo.architect" class="mr-3">
                  <strong>{{ $t('library.architect') }}:</strong> {{ library.buildingInfo.architect }}
                </span>
                <span v-if="library.buildingInfo.interiorDesigner" class="">
                  <strong>{{ $t('library.interior') }}:</strong> {{ library.buildingInfo.interiorDesigner }}
                </span>
                <p/>
              </section>
              <section v-if="!library.pictures.length && !library.description && !hasBuildingInfo()">
                <p>{{ $t('library.no-presentation') }}.</p>
              </section>
            </b-tab>
            <b-tab :active="selected_tab_name === 'tab_contact'">
              <template #title>{{ $t('library.tab-contact') }}</template>
              <section v-if="library.address" class="">
                <h2 class="mt-4">{{ $t("contact-info.contact-details") }}</h2>
                <h3 class="mt-3">{{ $t('library.address') }}</h3>
                <table class="table table-sm table-border">
                  <thead class="">
                    <tr>
                      <th class="">{{ $t('library.location') }}</th>
                      <template v-if="library.mailAddress"><th class="">{{ $t('library.location-mail') }}</th></template>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="pt-2">
                        <template v-if="library.name">{{ library.name }}<br/></template>
                        <template v-if="library.address.street">{{ library.address.street }}<br/></template>
                        <template v-if="library.address.zipcode">{{ library.address.zipcode }}</template>
                        <template v-if="library.address.city">{{ library.address.city }}</template>
                        <template v-if="library.address.area">({{ library.address.area }})</template><br/>
                        <span v-if="library.address.info" class="text-muted">{{ library.address.info }}</span>
                      </td>
                      <template v-if="library.mailAddress">
                        <td class="pt-2">
                          <template v-if="library.name">{{ library.name }}<br/></template>
                          <template v-if="library.mailAddress.boxNumber">{{ $t('library.pobox') }} {{ library.mailAddress.boxNumber}}<br/></template>
                          <template v-else-if="library.mailAddress.street">{{ library.mailAddress.street }}<br/></template>
                          <template v-if="library.mailAddress.zipcode">{{ library.mailAddress.zipcode }}</template>
                          <template v-if="library.mailAddress.area">{{ library.mailAddress.area.toUpperCase() }}</template>
                        </td>
                      </template>
                    </tr>
                  </tbody>
                </table>
                <map-view v-if="mapIsVisible" class="library-location" :pos="library.coordinates | coords"
                  :markers="[[library.name, [library.coordinates.lat, library.coordinates.lon]]]"/>
              </section>
              <section v-if="hasPublicTransportation()" class="border border-top-0 p-3 mb-4">
                <h3>{{ $t("contact-info.transit-directions") }}</h3>
                <p v-if="library.transitInfo.directions" class="mb-1">
                  {{ library.transitInfo.directions }}
                </p>
                <p v-if="library.transitInfo.buses" class="mb-1">
                  <strong>{{ $t("contact-info.buses") }}:</strong>
                  {{ library.transitInfo.buses }}
                </p>
                <p v-if="library.transitInfo.trams" class="mb-1">
                  <strong>{{ $t("contact-info.trams") }}:</strong>
                  {{ library.transitInfo.trams }}
                </p>
                <p v-if="library.transitInfo.trains" class="mb-1">
                  <strong>{{ $t("contact-info.trains") }}:</strong>
                  {{ library.transitInfo.trains }}
                </p>
                <p v-if="library.transitInfo.parking" class="mb-1">
                  <strong>{{ $t("contact-info.parking-instructions") }}:</strong>
                  {{ library.transitInfo.parking }}
                </p>
              </section>
              <section>
                <contact-info :library="library"/>
              </section>
            </b-tab>
            <b-tab :active="selected_tab_name === 'tab_services'">
              <template #title>{{ $t('library.tab-services') }}</template>
              <h2 class="mt-4">{{ $t('library.services') }}</h2>
              <section v-if="hasServices()" class="">
                <list-services :services="library.services"/>
              </section>
              <section v-else>
                <p>{{ $t('library.no-services') }}.</p>
              </section>
            </b-tab>
            <b-tab :active="selected_tab_name === 'tab_xs_schedules'">
              <template #title>{{ $t('library.tab-schedules') }}</template>
              </section>
              <section v-if="library.schedules.length > 0" class="d-md-none">
                <h2 class="mt-4">{{ $t('library.schedules') }}</h2>
                <schedules :schedules="library.schedules"/>
                <div v-if="periodInfo.length" class="period-info mt-3 pt-2">
                  <template v-for="period of periodInfo">
                    <p v-if="period.description" class="mb-1"><small>
                      <strong v-if="period.validUntil">
                        <date-time :date="period.validFrom" format="P" formal short/>–<date-time :date="period.validUntil" format="P" formal short/>:
                      </strong>
                      <strong v-else>
                        {{ $t('schedules.period-from') }} <date-time :date="period.validFrom" format="P" formal short/>:
                      </strong>
                      <span>{{ period.description }}</span>
                    </small></p>
                  </template>
                </div>
              </section>
            </b-tab>
          </b-tabs>

        </div><!-- End of col-md-6 col-xl-7 -->
        <!-- End of main column -->

        <!-- Sidebar -->
        <section v-if="library.schedules.length > 0" class="col-md-6 col-xl-5 d-none d-md-block">
          <h2 class="sr-only">{{ $t('library.schedules') }}</h2>
          <schedules :schedules="library.schedules"/>
          <div v-if="periodInfo.length" class="period-info mt-3 pt-2">
            <template v-for="period of periodInfo">
              <p v-if="period.description" class="mb-1"><small>
                <strong v-if="period.validUntil">
                  <date-time :date="period.validFrom" format="P" formal short/>–<date-time :date="period.validUntil" format="P" formal short/>:
                </strong>
                <strong v-else>
                  {{ $t('schedules.period-from') }} <date-time :date="period.validFrom" format="P" formal short/>:
                </strong>
                <span>{{ period.description }}</span>
              </small></p>
            </template>
          </div>
        </section>
        <!-- End of sidebar -->

      </div>
    </div>
  </main>

  <main v-else-if="hasError">
    <Page404/>
  </main>

  <main v-else>
    <div>
      <p class="mt-4 text-center">{{ $t('app.searching') }}...</p>
    </div>
    <div class="d-flex justify-content-center my-5">
      <div id="page-load-throbber" class="loader" aria-hidden="true"></div>
    </div>
  </main>
</template>

<script>
import Popper from 'popper.js'

import bPopover from 'bootstrap-vue'

import { format } from 'date-fns'
import DateTime from './DateTime.vue'

import ListServices from './ListServices'
import Schedules from './Schedules.vue'
import MapView from './MapView'
import Photos from './Photos'
import ContactInfo from './ContactInfo'
import Page404 from './Page404'

import { coordStr, formatDistance, kirkanta, addToMapArray } from '@/mixins'
import { faAddressCard, faExchangeAlt, faQuoteRight, faEnvelope, faLink, faLocationArrow } from '@fortawesome/free-solid-svg-icons'

import {
  faFacebookSquare,
  faFlickr,
  faInstagram,
  faPinterestSquare,
  faTwitterSquare,
  faVimeoSquare,
  faYoutube
} from '@fortawesome/free-brands-svg-icons'

const iconMap = new Map([
  ['facebook.com', faFacebookSquare],
  ['flickr.com', faFlickr],
  ['instagram.com', faInstagram],
  ['pinterest.com', faPinterestSquare],
  ['vimeo.com', faVimeoSquare],
  ['twitter.com', faTwitterSquare],
  ['youtube.com', faYoutube]
])

const someRegexp = new RegExp([...iconMap.keys()].join('|').replace('.', '\\.'))

export default {
  directives: { bPopover },
  components: { ContactInfo, ListServices, MapView, Photos, Schedules, DateTime, Page404 },
  data: () => ({
    activePopups: [],
    refs: {},
    library: null,
    consortium: null,
    faQuoteRight,
    faEnvelope,
    faLocationArrow,
    faAddressCard,
    faExchangeAlt,
    hasError: false,
    mapIsVisible: false,
    selected_tab_name: 'tab_xs_schedules'
  }),
  computed: {
    someLinks () {
      return this.library.links.filter((link) => someRegexp.test(link.url)).sort((a, b) => a.name.localeCompare(b.name))
    },
    serviceCategories () {
      const groups = new Map()

      for (let service of this.library.services) {
        addToMapArray(groups, service.type, service)
      }

      return [...groups]
    },
    periodInfo () {
      let filtered = []
      for (let pid in this.refs.period) {
        if (this.refs.period[pid].description) {
          filtered.push(this.refs.period[pid])
        }
      } 
      return filtered
    }
  },
  filters: {
    coords: (posObject) => {
      return [posObject.lat, posObject.lon]
    }
  },
  methods: {
    formatDistance,
    linkIcon (link) {
      let iconClass = faLink

      for (let [rx, icon] of iconMap) {
        if (link.url.match(rx)) {
          iconClass = icon
        }
      }

      return iconClass
    },
    hasPublicTransportation () {
      if (this.library.transitInfo) {
        for (let info of Object.values(this.library.transitInfo)) {
          if (info && info.length) {
            return true
          }
        }
      }
      return false
    },
    hasBuildingInfo () {
      if (this.library.founded) {
        return true
      } else if (this.library.buildingInfo) {
        for (let info of Object.values(this.library.buildingInfo)) {
          if (info && info.length) {
            return true
          }
        }
      }
      return false
    },
    hasContactInfo () {
      return (this.library.links.length + this.library.emailAddresses.length + this.library.phoneNumbers.length) > 0
    },
    hasServices () {
      return this.library.services.length > 0
    },
    onChangeTab (index) {
      this.mapIsVisible = index === 1
    },
    visibleHandler(isVisible) {
      if (isVisible) {
        this.selected_tab_name = 'tab_xs_schedules'
      } else {
        this.selected_tab_name = 'tab_presentation'
      }
    }
  },
  async created () {
    const params = {
      'city.slug': this.$route.params.city,
      slug: this.$route.params.library,
      with: ['departments', 'departments', 'emailAddresses', 'links', 'mailAddress', 'persons', 'pictures', 'phoneNumbers', 'primaryContactInfo', 'schedules', 'services', 'transitInfo', 'buildingInfo'],
      refs: ['city', 'consortium', 'period'],
      status: '',
      'period.start': '0w',
      'period.end': '8w'
    }

    try {
      let pos = await this.$location.query(true)

      Object.assign(params, {
        'geo.pos': coordStr(pos.coords)
      })
    } catch (error) {
      // pass
    }

    try {
      let response = await kirkanta.get('library', params, {}, true)

      this.library = response.data
      this.refs = response.refs
      this.consortium = this.refs.consortium[this.library.consortium];
    } catch (error) {
      this.hasError = true
    }

    // Fetch additional finna_data if the consortium is a Finna organisation
    if (this.library.consortium && this.library.consortium > 0 && !this.refs.consortium[this.library.consortium]){
      
      try {
        let response = await kirkanta.get('finna_organisation', this.library.consortium, {}, true);
        this.consortium = response.data;
      } catch (error) {
        // This is an additional non-mandatory data request. No need to mark this as an error.
      }

    }
  }
}
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  .fa-quote-right {
    margin-right: spacing(2);
  }

  .cover-photo-frame {
    display: flex;
    flex-flow: column;
    justify-content: center;
    padding-left: 0;

    height: 320px;
    overflow: hidden;
  }

  .cover-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }

  .info-link {
    display: inline-block;
    margin-right: spacing(3);
  }

  .photos {
    max-height: 100%;
  }

  .col-department {
    width: 250px;
  }

  .library-location {
    height: 300px;
    overflow: hidden;
  }
  
  .consortium {
    p {
      line-height: 1
    }
  }

  .period-info {
    border-top: 1px dashed $table-border-color;
    line-height: 1.25;
  }
</style>

<style lang="scss" scoped>
  .tabs-library-content {
    display: flex;
    flex-flow: column;

    .tab-content {
      flex: 1 1 auto;

      .tab-pane.show {
        height: 100%;
      }
    }
  }

  #page-load-throbber.loader {
    font-size: .5rem;
    -webkit-transform: translateZ(0);
    -ms-transform: translateZ(0);
    transform: translateZ(0) scale(1, 1);
  }

  .fa-facebook-square {
    color: #3b5998;
  }

  .fa-twitter-square {
    color: #1da1f2;
  }

  .fa-instagram {
    background-color: #904ac6;
    color: white;
    padding: 0 1px;
    box-sizing: content-box;
  }

  .fa-pinterest-square {
    color: #d63633;
  }

  .fa-youtube {
    color: red;
  }

  .fa-vimeo {
    color: #1ab7ea;
  }

  .fa-flickr {
    color: #e62683;
  }

  .fa-link {
    color: #444;
  }
</style>
