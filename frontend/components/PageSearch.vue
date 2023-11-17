<template>
  <main>
    <form @submit.prevent="submit">
      <div class="row">
        <div class="col-lg-9" id="quick-search">
          <b-form-group id="query-field" breakpoint="lg" :label="$t('search.placeholder')" label-class="sr-only" class="pt-3">
            <b-input-group>
              <label for="searchinput" class="sr-only">{{ $t('search.placeholder') }}</label>
              <b-form-input size="lg" :placeholder="$t('search.placeholder')" id="searchinput" v-model="form.q"/>

              <b-input-group-append>
                <div v-if="busy" class="loader" id="form-input-throbber" aria-hidden="true">{{ $t('app.searching') }}...</div>

                <b-btn variant="primary" type="submit">
                  <span class="sr-only">{{ $t('search.search-button') }}</span>
                  <fa :icon="faSearch"/>
                </b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-3 order-lg-3" id="sidebar">
          <button type="button" class="d-block d-lg-none btn btn-link toggle-form-advanced" @click="expandFormMobile = !expandFormMobile">
            {{ $t('search.advanced') }}
            <fa :icon="expandFormMobile ? faPlusSquare : faPlusSquareReg" class="ml-1"/>
          </button>

          <fieldset id="advanced-search" :data-expanded="expandFormMobile">
            <legend class="h1 d-none d-lg-block">{{ $t('search.advanced') }}</legend>
            <b-form-group :label="$t('search.options')">
              <b-form-checkbox id="toggle-gps-1" v-model="locationChecked">{{ $t('search.use-location') }}</b-form-checkbox>
              <b-form-checkbox id="only-open-libraries" v-model="form.status" value="open" unchecked-value="">{{ $t('search.only-open') }}</b-form-checkbox>
            </b-form-group>
            <b-form-group id="library-type" :label="$t('search.library-type')">
              <b-form-checkbox-group id="library-type-options" v-model="form.type" :options="libraryTypes"/>
            </b-form-group>
          </fieldset>
        </div>
        <div class="col-lg-9 pt-3" id="search-results">
          <b-list-group>
            <b-list-group-item v-for="library in libraries" :key="library.id" class="library-card border-top-0 border-left-0 border-right-0 border-bottom px-0 pb-4 mb-2">
              <div class="library-card-photo-frame">
                <api-image :file="library.coverPhoto" alt="" class="library-card-photo"/>
              </div>
              <div class="library-card-body">
                <router-link :to="routeToLibrary(library)" :class="'h4 font-weight-bold'">{{ library.name }}</router-link>
                <div class="text-uppercase">{{ cityName(library) }}</div>
                <div>{{ libraryAddress(library) }}</div>
              </div>
              <div class="library-card-aside">
                <div class="library-card-status">
                  <b-badge v-if="library.liveStatus == 0" variant="danger">{{ $t('schedules.closed') }}</b-badge>
                  <b-badge v-if="library.liveStatus == 1" variant="success">{{ $t('schedules.open') }}</b-badge>
                  <b-badge v-if="library.liveStatus == 2" variant="info">{{ $t('schedules.self-service') }}</b-badge>
                  <b-badge v-if="library.distance" variant="light">{{ formatDistance(library.distance) }}</b-badge>
                </div>
                <span class="library-card-live mt-1">
                  <!-- Always render container to push rest of the content down -->
                  <template v-if="hasOpeningTime(first(library.schedules)) && hasNotYetOpened(first(library.schedules))">
                    {{ $t('schedules.opens-at') }} <date-time :time="first(library.schedules) | opens" format="p" formal/>
                  </template>
                  <template v-else-if="hasOpeningTime(first(library.schedules))">
                    <date-time :time="first(library.schedules) | opens" format="p" formal/>–<date-time :time="first(library.schedules) | closes" format="p" formal/>
                  </template>
                  <span v-if="library.schedules.length && !hasOpeningTime(first(library.schedules))" class="text-muted">
                    {{ $t('schedules.check-schedules') }}
                  </span>
                </span>
              </div>
            </b-list-group-item>
          </b-list-group>
          <div class="text-center">
            <scroll-guard @scroll="loadMore" :enabled="canLoadMore && userLoadedMore && !busy"/>

            <button type="button" class="btn btn-lg btn-link my-3" id="btn-load-more" @click="loadMore()" v-if="canLoadMore">
              {{ $t('search.load-more') }}
              <span v-if="busy" class="loader" id="form-submit-throbber" aria-hidden="true">{{ $t('app.searching') }}...</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </main>
</template>

<script>
import { kirkanta, formatDistance, first, last } from '@/mixins'
import { faPlusSquare, faSearch } from '@fortawesome/free-solid-svg-icons'
import { faPlusSquare as faPlusSquareReg } from '@fortawesome/free-regular-svg-icons'
import DateTime from './DateTime'
import ScrollGuard from './ScrollGuard'

const dateNow = new Date()
const hoursNow = dateNow.getHours()
const minutesNow = dateNow.getMinutes()

export default {
  components: { DateTime, ScrollGuard },
  data: () => ({
    faPlusSquare,
    faPlusSquareReg,
    faSearch,
    searchOptions: {
      skip: 0,
      limit: 10
    },
    form: {
      with: ['schedules'],
      refs: ['city'],
      q: null,
      type: [],
      status: '',
      'geo.pos': null,
      'period.start': '0d',
      'period.end': '1d'
    },
    timers: {
      submit: null
    },
    expandFormMobile: false,
    locationChecked: false,
    userLoadedMore: false,
    canLoadMore: true,
    libraryTypes: [],
    libraries: [],
    cities: {},
    busy: true
  }),
  computed: {
    useLocation () {
      let formState = this.locationChecked
      let globalState = this.$location.enabled
      return formState && globalState
    }
  },
  methods: {
    formatDistance,
    first,
    last,
    cityName (library) {
      let city = this.cities[library.city]
      return library.address.area ? `${city.name} (${library.address.area})` : city.name
    },
    libraryAddress (library) {
      if (library.address) {
        let a = library.address
        return `${a.street}`
      }
    },
    routeToLibrary (library) {
      return {
        name: 'library.show',
        params: {
          city: this.cities[library.city].slug,
          library: library.slug
        }
      }
    },
    loadMore () {
      this.userLoadedMore = true
      this.searchOptions.skip += this.searchOptions.limit
      this.submit(true)
    },
    async submit (append = false) {
      this.busy = true

      if (this.useLocation) {
        try {
          let pos = await this.$location.query()
          this.form['geo.pos'] = `${pos.coords.latitude},${pos.coords.longitude}`
        } catch (error) {
          console.warn('geolocation disabled')
        }
      } else {
        this.form['geo.pos'] = undefined
      }

      let options = {
        limit: this.searchOptions.limit,
        skip: append ? this.searchOptions.skip : 0
      }
      let query = Object.assign(options, this.form)
      let response = await kirkanta.search('library', query)

      if (append) {
        this.canLoadMore = response.items.length > 0
        this.libraries.push(...response.items)
        Object.assign(this.cities, response.refs.city)
      } else {
        this.canLoadMore = true
        this.userLoadedMore = false
        this.searchOptions.skip = 0
        this.libraries = response.items
        this.cities = response.refs.city
      }

      this.busy = false
    },
    hasOpeningTime (day) {
      return day && day.times.length > 0
    },
    hasNotYetOpened (day) {
      if (day.times.length) {
        if (first(day.times).from.substring(0, 2) > hoursNow || (first(day.times).from.substring(0, 2) == hoursNow && first(day.times).from.substring(3, 5) > minutesNow) ) {
          return true
        }
      }
    }
  },
  filters: {
    opens (day) {
      if (day.times.length) {
        return first(day.times).from
      }
    },
    closes (day) {
      if (day.times.length) {
        return last(day.times).to
      }
    },
    closed (day) {
      return day.closed === true
    }
  },
  watch: {
    form: {
      deep: true,
      handler () {
        if (this.timers.submit) {
          clearTimeout(this.timers.submit)
          this.timers.submit = 0
        }

        this.timers.submit = setTimeout(this.submit, 500)
      }
    },
    'locationChecked': {
      handler (state) {
        this.submit()
        this.$session.set('search_page.location', !!state)
      }
    }
  },
  async created () {
    this.libraryTypes = [
      { text: this.$t('library.type.municipal'), value: 'municipal children music' },
      { text: this.$t('library.type.mobile'), value: 'mobile' },
      { text: this.$t('library.type.polytechnic'), value: 'polytechnic' },
      { text: this.$t('library.type.university'), value: 'university' },
      { text: this.$t('library.type.special'), value: 'special' },
      { text: this.$t('library.type.other'), value: 'home_service institutional vocational_college school' }
    ]

    let formLocationStatus = this.$session.get('search_page.location')

    if (formLocationStatus === undefined) {
      this.locationChecked = this.$location.enabled
    } else {
      this.locationChecked = !!formLocationStatus
    }
    this.submit()

    this.$location.$on('enabled', () => {
      this.locationChecked = true
    })

    this.$location.$on('disabled', () => {
      this.locationChecked = false
    })
  }
}
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  #form-input-throbber {
    position: absolute;
    font-size: 36px;
    margin-left: -1em;
    margin-top: 5px;
    z-index: $zindex-tooltip;
  }

  #btn-load-more {
    position: relative;
  }

  #form-submit-throbber {
    position: absolute;
    right: -0.7rem;
    top: 0.7rem;
    z-index: $zindex-tooltip;
  }

  .library-card {
    height: 4.7rem;
    border-bottom: 1px solid $border-color;
    padding: spacing(2);
    box-sizing: content-box;

    display: flex;
  }

  .library-card-photo-frame {
    overflow: hidden;
    text-align: center;
    margin-right: spacing(2);
    flex-basis: 40px;

    @include media-breakpoint-up("sm") {
      flex-basis: 80px;
    }

    @include media-breakpoint-up("md") {
      flex-basis: 120px;
    }

    @include media-breakpoint-up("lg") {
      flex-basis: 140px;
    }
  }

  .library-card-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    border-radius: $border-radius-sm;
  }

  .library-card-body {
    margin-right: spacing(2);
    flex: 1 1;
    white-space: nowrap;
    overflow: hidden;

    a {

      @include media-breakpoint-down("sm") {
        font-size: 1rem;
      }
    }
  }

  .library-card-aside {
    display: flex;
    flex-flow: column;
  }

  .library-card-live {
    text-align: right;
  }

  .library-card-status {
    text-align: right;
  }

  @include media-breakpoint-up("lg") {
    #sidebar {
      background-color: $sidebar-bg;
      margin-top: -5.05rem;
    }

    #advanced-search {
      margin-top: 1.5rem;
      position: sticky;
      top: 4.8rem;

      > .col-form-label {
        font-size: x-large;
      }
    }
  }

  @include media-breakpoint-down("md") {
    #sidebar {
      padding-bottom: spacing(3);
    }

    #advanced-search {
      display: none;
      padding-left: spacing(1);
      margin-bottom: -1 * spacing(3);

      &[data-expanded] {
        display: block;
      }
    }

    .toggle-form-advanced {
      margin-left: -1 * spacing(2);
    }
  }
</style>
