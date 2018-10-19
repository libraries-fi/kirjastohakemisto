<template>
  <main>
    <form @submit.prevent="onSubmit">
      <div class="row">
        <div class="col-lg-9" id="quick-search">
          <b-form-group id="query-field" breakpoint="lg" :label="$t('search.placeholder')" label-class="sr-only" class="pt-3">
            <b-input-group>
              <b-form-input size="lg" :placeholder="$t('search.placeholder')" v-model="form.q" v-focus/>
              <b-btn variant="primary" type="submit">
                <fa :icon="faSearch"/>
              </b-btn>
            </b-input-group>
          </b-form-group>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-3 order-lg-3" id="sidebar">
          <b-form-group id="advanced-search" :label="$t('search.advanced')" label-class="h1" >
            <b-form-group :label="$t('search.options')">
              <b-form-checkbox id="toggle-gps-1" v-model="options.gps">{{ $t('search.use-location') }}</b-form-checkbox>
              <b-form-checkbox id="only-open-libraries" v-model="form.status" value="open" unchecked-value="">{{ $t('search.only-open') }}</b-form-checkbox>
            </b-form-group>
            <b-form-group id="library-type" :label="$t('search.library-type')">
              <b-form-checkbox-group id="library-type-options" v-model="form.type" :options="libraryTypes"/>
            </b-form-group>
          </b-form-group>
        </div>
        <div class="col-lg-9" id="search-results">
          <ul class="list-unstyled">


            <li v-for="library in libraries" class="row library-card py-2">
              <div class="library-card-photo-frame">
                <api-image :file="library.coverPhoto" alt="" class="library-card-photo"/>
              </div>
              <div class="library-card-body">
                <router-link :to="routeToLibrary(library)">{{ library.name }}</router-link>
                <div class="text-uppercase">{{ cityName(library) }}</div>
                <div>{{ libraryAddress(library) }}</div>
              </div>
              <div class="library-card-aside" style="display: flex; flex-flow: column; justify-content: space-between">
                <div>
                  <b-badge v-if="library.liveStatus == 0" variant="danger">closed</b-badge>
                  <b-badge v-if="library.liveStatus == 1" variant="success">open</b-badge>
                  <b-badge v-if="library.liveStatus == 2" variant="info">self-service</b-badge>
                  <b-badge v-if="library.distance" variant="primary">{{ formatDistance(library.distance) }}</b-badge>
                </div>

                <div v-if="library.liveStatus !== null">
                  {{ first(first(library.schedules).times).from }} – {{ last(first(library.schedules).times).to }}
                </div>
              </div>
            </li>


          </ul>
        </div>
      </div>
    </form>
  </main>
</template>

<script>
  import { kirkanta, formatDistance, geolocation, first, last } from '@/mixins'
  import { faSearch } from '@fortawesome/free-solid-svg-icons'

  export default {
    data: () => ({
      faSearch,
      form: {
        with: ['schedules'],
        refs: ['city'],
        q: null,
        type: [],
        status: '',
        'geo.pos': null,
        'geo.dist': 100,
        'period.start': '0d',
        'period.end': '1d',
      },
      timers: {
        submit: null
      },
      options: {
        gps: false,
        onlyOpen: false,
      },
      libraryTypes: [],
      libraries: [],
      cities: {}
    }),
    methods: {
      formatDistance,
      first,
      last,
      cityName(library) {
        let city = this.cities[library.city]
        return library.address.area ? `${city.name} (${library.address.area})` : city.name
      },
      libraryAddress(library) {
        if (library.address) {
          let a = library.address
          return `${a.street}`
        }
      },
      routeToLibrary(library) {
        return {
          name: 'library.show',
          params: {
            city: this.cities[library.city].slug,
            library: library.slug
          }
        }
      },
      async onSubmit() {
        try {
          await geolocation.test()
          let pos = await geolocation.gps()
          this.form['geo.pos'] = `${pos.coords.latitude},${pos.coords.longitude}`
        } catch (err) {
          console.warn('geolocation disabled')
        }

        kirkanta.search('library', this.form).then((response) => {
          this.libraries = response.items
          this.cities = response.refs.city
        })
      }
    },
    watch: {
      form: {
        deep: true,
        handler() {
          if (this.timers.submit) {
            clearTimeout(this.timers.submit)
            this.timers.submit = 0
          }

          this.timers.submit = setTimeout(this.onSubmit, 500)
        },
      },
    },
    created() {
      this.onSubmit()

      this.libraryTypes = [
        { text: this.$t('library.municipal'), value: 'library main_library mobile regional' },
        { text: this.$t('library.polytechnic'), value: 'polytechnic' },
        { text: this.$t('library.university'), value: 'university' },
        { text: this.$t('library.special'), value: 'special' },
        { text: this.$t('library.other'), value: 'home_service institutional children other' },
      ]
    },
  }
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

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

  @include media-breakpoint-up("lg") {
    #sidebar {
      background-color: $sidebar-bg;
      margin-top: -5.45rem;
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
