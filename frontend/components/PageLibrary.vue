<template>
  <main v-if="library" class="pt-3">
    <div class="visual-section">
      <h1>
        {{ library.name }}
        <b-badge v-if="library.distance" variant="primary" class="float-right">{{ formatDistance(library.distance) }}</b-badge>
      </h1>
      <blockquote v-if="library.slogan">
        <fa :icon="faQuoteRight" aria-hidden="true"/>
        {{ library.slogan }}
      </blockquote>
    </div>

    <div class="visual-section">
      <div class="row">
        <div class="col-md-6 col-xl-7">
          <b-tabs class="tabs-photos-map" @input="onChangeTab">
            <b-tab title="Photos" v-if="library.pictures.length" active>
              <section class="cover-photo-frame">
                <h2 class="sr-only">{{ $t('library.photos') }}</h2>
                <!-- <api-image :file="library.coverPhoto" size="medium" alt="" class="cover-photo"/> -->

                <photos :source="library.pictures"/>
              </section>
            </b-tab>
            <b-tab title="Map">
              <map-view v-if="mapIsVisible" class="library-location" :pos="library.coordinates | coords"
                :markers="[[library.name, [library.coordinates.lat, library.coordinates.lon]]]"/>
            </b-tab>
          </b-tabs>
        </div>

        <section v-if="library.schedules.length > 0" class="col-md-6 col-xl-5">
          <h2 class="sr-only">{{ $t('library.schedules') }}</h2>
          <schedules :schedules="library.schedules"/>
        </section>
      </div>
    </div>

    <section v-if="library.address" class="visual-section">
      <div class="row">
        <h2 class="sr-only">{{ $t("contact-info.contact-details") }}</h2>

        <div class="col-md-4">
          <h3 class="h2">
            <fa :icon="faLocationArrow"/>
            {{ $t('library.location') }}
          </h3>
          <address>
            <p>
              {{ library.address.street }}, {{ library.address.zipcode }} {{ library.address.city }} <template v-if="library.address.area">({{ library.address.area }})</template><br/>
              <span v-if="library.address.info" class="text-muted">{{ library.address.info }}</span>
            </p>

            <p v-if="library.email">
              <b>{{ library.email.name }}</b><br/>
              <a :href="'mailto:' + library.email.email">{{ library.email.email }}</a><br/>
            </p>

            <p v-if="library.phone">
              <b>{{ library.phone.name }}</b><br/>
              <a :href="'tel:+358' + library.phone.number.replace(/\D/g, '').substr(1)">{{ library.phone.number }}</a>
            </p>
          </address>
        </div>

        <div class="col-md-4">
          <div v-if="library.mailAddress">
            <h3 class="h2">
              <fa :icon="faEnvelope"/>
              {{ $t('library.location-mail') }}
            </h3>
            <p>
              {{ library.name }}<br/>
              <template v-if="library.mailAddress.street">{{ library.mailAddress.street }}<br/></template>
              <template v-if="library.mailAddress.box_number">P.O. Box {{ library.mailAddress.box_number}}<br/></template>
              <template>{{ library.mailAddress.zipcode }} {{ library.mailAddress.area.toUpperCase() }}<br/></template>
            </p>
          </div>
        </div>

        <div v-if="consortium" class="col-md-4">
          <h3>
            <fa :icon="faExchangeAlt"/>
            {{ $t('library.consortium') }}
          </h3>
          <p>
            {{ consortium.name }}</br>
            <a :href="consortium.homepage" class="external-link">{{ $t('library.web-library') }}</a>
          </p>
        </div>
      </div>
    </section>

    <section v-if="library.links" class="info-links visual-section">
      <h2 class="sr-only">{{ $t("library.other-links") }}</h2>
      <a v-for="link in someLinks" :href="link.url" class="info-link">
        <fa v-if="linkIcon(link)" :icon="linkIcon(link)"/>
        {{ link.name }}
      </a>
    </section>

    <div v-html="library.description" class="text-justify visual-section"/>

    <section v-if="hasPublicTransportation()">
      <h2 class="sr-only">{{ $t("Transit directions") }}</h2>
      <h3>{{ $t("Public transportation") }}</h3>

      <div class="row">
        <div v-if="library.transit.buses" class="col-md-2">
          <h4>{{ $t("Buses") }}</h4>
          <p>{{ library.transit.buses }}</p>
        </div>
        <div v-if="library.transit.trams" class="col-md-2">
          <h4>{{ $t("Trams") }}</h4>
          <p>{{ library.transit.trams }}</p>
        </div>
        <div v-if="library.transit.trains" class="col-md-2">
          <h4>{{ $t("Trains") }}</h4>
          <p>{{ library.transit.trains }}</p>
        </div>
      </div>

      <div v-if="library.transit.parking">
        <h3>{{ $t("Parking instructions") }}</h3>
        {{ library.transit.parking }}
      </div>
    </section>

    <section v-if="hasContactInfo()" class="visual-section">
      <h2>
        <fa :icon="faAddressCard"/>
        {{ $t('contact-info.contact-details')}}
      </h2>
      <contact-info :library="library"/>
    </section>

    <section v-if="hasServices()" class="visual-section">
      <list-services :services="library.services"/>
    </section>
  </main>
</template>

<script>
  import Popper from 'popper.js'
  import bPopover from 'bootstrap-vue/es/directives/popover/popover'

  import ListServices from './ListServices'
  import Schedules from './Schedules.vue'
  import MapView from './MapView'
  import Photos from './Photos'
  import ContactInfo from './ContactInfo'

  import { coordStr, geolocation, formatDistance, kirkanta } from '@/mixins'
  import { faAddressCard, faExchangeAlt, faQuoteRight, faEnvelope, faLink, faLongArrowAltLeft, faLocationArrow } from '@fortawesome/free-solid-svg-icons'

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
    ['youtube.com', faYoutube],
  ]);

  const someRegexp = new RegExp([...iconMap.keys()].join('|').replace('.', '\\.'))

  export default {
    directives: { bPopover },
    components: { ContactInfo, ListServices, MapView, Photos, Schedules },
    data: () => ({
      activePopups: [],
      refs: {},
      library: null,
      mapIsVisible: false,
      faQuoteRight,
      faEnvelope,
      faLocationArrow,
      faAddressCard,
      faExchangeAlt
    }),
    computed: {
      someLinks() {
        return this.library.links.filter((link) => someRegexp.test(link.url)).sort((a, b) => a.name.localeCompare(b.name))
      },
      serviceCategories() {
        const groups = new Map

        for (let service of this.library.services) {
          addToMapArray(groups, service.type, service)
        }

        return [...groups]
      },
      consortium () {
        return this.refs.consortium[this.library.consortium]
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
        let icon_class = faLink;

        for (let [rx, icon] of iconMap) {
          if (link.url.match(rx)) {
            icon_class = icon;
          }
        }

        return icon_class;
      },
      hasPublicTransportation () {
        if (this.library.transit) {
          for (let [field, info] of Object.entries(this.library.transit)) {
            if (info && info.length) {
              return true;
            }
          }
        }
        return false;
      },
      hasContactInfo () {
        return (this.library.links.length + this.library.emailAddresses.length + this.library.phoneNumbers.length) > 0
      },
      hasServices () {
        return this.library.services.length > 0
      },
      onChangeTab (index) {
        this.mapIsVisible = index == 1
        // this.mapIsVisible = true
      }
    },
    async created() {
      const params = {
        'city.slug': this.$route.params.city,
        slug: this.$route.params.library,
        with: ['departments', 'departments', 'emailAddresses', 'links', 'mailAddress', 'persons', 'pictures', 'phoneNumbers', 'schedules', 'services'],
        refs: ['city', 'consortium', 'period'],
        status: '',
        'period.start': '0w',
        'period.end': '8w'
      }

      try {
        let pos = await geolocation.tryGps()

        Object.assign(params, {
          'geo.pos': coordStr(pos.coords),
        })
      } catch (err) {
        // pass
      }

      let response = await kirkanta.get('library', params)

      this.library = response.data
      this.refs = response.refs
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

  .visual-section {
    padding-left: spacing(3);
    margin-left: -1 * spacing(3);
    border-left: 5px solid black;
    margin-bottom: spacing(3);

    &:nth-child(1) {
      border-color: orange;
    }

    &:nth-child(2) {
      border-color: rgb(87, 129, 238);
    }

    &:nth-child(3) {
      border-color: rgb(195, 195, 195);
    }

    &:nth-child(4) {
      border-color: rgb(167, 244, 135);
    }

    &:nth-child(5) {
      border-color: rgb(219, 152, 255);
    }

    &:nth-child(6) {
      border-color: rgb(164, 102, 112);
    }
  }

  .col-department {
    width: 250px;
  }

  .library-location {
    height: 300px;
    overflow: hidden;
  }
</style>

<style lang="scss">
  .tabs-photos-map {
    height: 100%;
    display: flex;
    flex-flow: column;

    .tab-content {
      flex: 1 1 auto;

      .tab-pane.show {
        height: 100%;
      }
    }
  }
</style>

<style lang="scss" scoped>
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
