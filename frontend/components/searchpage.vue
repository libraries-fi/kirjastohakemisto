<template>
  <main>
    <form @submit.prevent="onSubmit">
      <div class="row">
        <div class="col-lg-9" id="quick-search">
          <b-form-group id="query-field" breakpoint="lg" :label="$t('search.placeholder')" label-class="sr-only" class="pt-3">
            <b-input-group>
              <b-form-input size="lg" :placeholder="$t('search.placeholder')" name="name" v-model="form.name" v-focus/>
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
              <b-form-checkbox-group id="library-type-options" name="type" v-model="form.type" :options="libraryTypes"/>
            </b-form-group>
          </b-form-group>
        </div>
        <div class="col-lg-9" id="search-results">
          <ul class="list-unstyled">
            <li v-for="library in libraries" class="row library-card">
              <div class="col-md-3 library-card-photo-frame">
                <api-image :file="library.coverPhoto" alt="" class="library-card-photo"/>
              </div>
              <div class="col-md-9">
                <router-link :to="routeToLibrary(library)">{{ library.name }}</router-link>
                <p class="text-uppercase m-0">{{ cityName(library) }}</p>
                <p>{{ libraryAddress(library) }}</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </form>
  </main>
</template>

<script>
  import { kirkanta, geolocation } from '@/mixins'
  import { faSearch } from '@fortawesome/free-solid-svg-icons'

  export default {
    data: () => ({
      faSearch,
      form: {
        refs: ['city'].join('+'),
        name: null,
        type: null,
        status: '',
        'geo.pos': null,
        'geo.dist': 100,
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
          this.libraries = response.result
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
    height: 120px;
    border-bottom: 1px solid $border-color;
    padding-top: spacing(2);
    padding-bottom: spacing(2);
  }

  .library-card-photo-frame {
    width: 120px;
    overflow: hidden;
    text-align: center;
  }

  .library-card-photo {
    object-fit: cover;
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
