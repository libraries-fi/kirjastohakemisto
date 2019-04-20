<template>
  <main>
    <form @submit.prevent="submit">
      <div class="row">
        <div class="col-lg-9" id="quick-search">
          <b-form-group id="query-field" breakpoint="lg" :label="$t('search.placeholder')" label-class="sr-only" class="pt-3">
            <b-input-group>
              <b-form-input size="lg" :placeholder="$t('search.placeholder')" v-model="form.q" v-focus/>

              <b-input-group-append>
                <div v-if="busy" class="loader" id="form-input-throbber" aria-hidden="true">Loading</div>

                <b-btn variant="primary" type="submit">
                  <fa :icon="faSearch"/>
                </b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-3 order-lg-3" id="sidebar">
          <b-form-group id="advanced-search" :label="$t('search.advanced')" label-class="h1" >
            <b-form-group :label="$t('search.options')">
              <b-form-checkbox id="toggle-gps-1" v-model="options.locationChecked">{{ $t('search.use-location') }}</b-form-checkbox>
              <b-form-checkbox id="only-open-libraries" v-model="form.status" value="open" unchecked-value="">{{ $t('search.only-open') }}</b-form-checkbox>
            </b-form-group>
            <b-form-group id="library-type" :label="$t('search.library-type')">
              <b-form-checkbox-group id="library-type-options" v-model="form.type" :options="libraryTypes"/>
            </b-form-group>
          </b-form-group>
        </div>
        <div class="col-lg-9" id="search-results">
          <b-list-group>
            <b-list-group-item v-for="library in libraries" :key="library.id" class="library-card border-0 px-0 my-2">
              <div class="library-card-photo-frame">
                <api-image :file="library.coverPhoto" alt="" class="library-card-photo"/>
              </div>
              <div class="library-card-body">
                <router-link :to="routeToLibrary(library)">{{ library.name }}</router-link>
                <div class="text-uppercase">{{ cityName(library) }}</div>
                <div>{{ libraryAddress(library) }}</div>
              </div>
              <div class="library-card-aside">
                <span class="library-card-live">
                  <!-- Always render container to push rest of the content down -->
                  <!-- <template v-if="library.liveStatus !== null">
                    <date-time :time="first(library.schedules) | opens" format="p"/>
                      –
                    <date-time :time="first(library.schedules) | closes" format="p"/>
                  </template> -->
                </span>
                <div class="text-right">
                  <b-badge v-if="library.liveStatus == 0" variant="danger">closed</b-badge>
                  <b-badge v-if="library.liveStatus == 1" variant="success">open</b-badge>
                  <b-badge v-if="library.liveStatus == 2" variant="info">self-service</b-badge>
                  <b-badge v-if="library.distance" variant="primary">{{ formatDistance(library.distance) }}</b-badge>
                </div>
              </div>
            </b-list-group-item>
          </b-list-group>
          <div class="text-center">
            <scroll-guard @scroll="loadMore" :enabled="userLoadedMore && !busy"/>

            <button type="button" class="btn btn-lg btn-link my-3" id="btn-load-more" @click="loadMore()">
              {{ $t('search.load-more') }}
              <span v-if="busy" class="loader" id="form-submit-throbber" aria-hidden="true">Loading</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </main>
</template>

<script>
import { kirkanta, formatDistance, first, last } from '@/mixins'
import { faSearch } from '@fortawesome/free-solid-svg-icons'
import DateTime from './DateTime'
import ScrollGuard from './ScrollGuard'

export default {
  components: { DateTime, ScrollGuard },
  data: () => ({
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
    options: {
      locationChecked: false,
      onlyOpen: false
    },
    userLoadedMore: false,
    libraryTypes: [],
    libraries: [],
    cities: {},
    busy: true
  }),
  computed: {
    useLocation: {
      get () {
        // let formState = this.$session.get('search_page.location')
        let formState = this.options.locationChecked
        let globalState = this.$location.enabled

        if (formState === undefined) {
          return globalState
        } else {
          return formState && globalState
        }
      },
      set (state) {
        this.$session.set('search_page.location', !!state)
      }
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
        this.libraries.push(...response.items)
        Object.assign(this.cities, response.refs.city)
      } else {
        this.searchOptions.skip = 0
        this.libraries = response.items
        this.cities = response.refs.city
      }

      this.busy = false
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
      // console.log("DAY", day)
      // return true
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
    'options.locationChecked': {
      handler () {
        this.submit()
      }
    }
  },
  async created () {
    this.libraryTypes = [
      { text: this.$t('library.type.municipal'), value: 'library main_library mobile regional' },
      { text: this.$t('library.type.polytechnic'), value: 'polytechnic' },
      { text: this.$t('library.type.university'), value: 'university' },
      { text: this.$t('library.type.special'), value: 'special' },
      { text: this.$t('library.type.other'), value: 'home_service institutional children other' }
    ]

    this.options.locationChecked = this.$session.get('search_page.location')
    this.submit()

    this.$location.$on('enabled', () => {
      this.options.locationChecked = true
    })

    this.$location.$on('disabled', () => {
      this.options.locationChecked = false
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
  }

  .library-card-aside {
    display: flex;
    flex-flow: column;
    justify-content: space-between;
  }

  .library-card-live {
    // font-size: $font-size-lg;
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
</style>
