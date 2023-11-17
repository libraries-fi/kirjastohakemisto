<template>
  <main v-if="consortium">
    <h1 class="page-title">{{ consortium.name }}</h1>
    <div v-if="consortium.logo" class="text-center text-md-right mt-5 mb-3 mt-md-n5 mb-md-n3" style="min-height: 75px;">
      <api-image :file="consortium.logo" alt="Consortium logo." size="medium" class="img-fluid" style="max-width: 175px;" aria-hidden="true"/>
    </div>
    <ul>
      <li>
        <a :href="consortium.homepage" class="d-block external-link">{{ $t('consortium.homepage') }}</a>
      </li>
    </ul>

    <div v-if="regions" class="map-container">
      <map-view ref="map" :regions="regions" :zoom="4" :markers="markers"/>
    </div>

    <section class="mt-3">
      <h2>{{ $t('consortium.city-count', {count: this.cities.length}) }}</h2>
      <ul>
        <li v-for="city in cities">
          {{ city.name }}
        </li>
      </ul>
    </section>

    <section class="mt-3">
      <h2>{{ $t('consortium.library-count', {count: this.libraries.length}) }}</h2>

      <ul>
        <li v-for="library in libraries">
          <router-link :to="{name: 're', params: {slug: library.slug}}">{{ library.name }}</router-link>
        </li>
      </ul>
    </section>
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
import { constants, detectLanguage, kirkanta } from '@/mixins'
import axios from 'axios'
import MapView from './MapView'
import Page404 from './Page404'

const API_BOUNDARY_URL = '/backend/regions'

export default {
  components: { MapView, Page404 },
  data: () => ({
    consortium: null,
    cities: [],
    libraries: [],
    regions: null,
    markers: null,
    hasError: false
  }),
  async created () {
    const cid = this.$route.params.consortium

    try {
      let pConsortiums = await kirkanta.get('consortium', {
        slug: cid
      }, {}, true)

      let pCities = kirkanta.search('city', {
        'consortium.slug': cid,
        limit: constants.AllResults,
        sort: 'name'
      })

      let pLibraries = kirkanta.search('library', {
        'consortium.slug': cid,
        limit: constants.AllResults,
        sort: 'name'
      })

      let [rConsortiums, rCities, rLibraries] = await Promise.all([pConsortiums, pCities, pLibraries])

      this.consortium = rConsortiums.data
      this.cities = rCities.items
      this.libraries = rLibraries.items

      this.markers = this.libraries.filter(l => l.coordinates).map((library) => {
        if (library.coordinates) {
          return [library.name, library.coordinates]
        }
      })

      let pBoundaries = await axios.get(API_BOUNDARY_URL, {
        params: {
          lang: detectLanguage(),
          q: this.cities.map((city) => city.name).join(' ')
        }
      })

      /**
       * Each boundary of a municipality is an array of polygons (often just 1 but sometimes more)
       */
      const regions = Object.values(pBoundaries.data).reduce((acc, b) => {
        acc.push(...b)
        return acc
      }, [])

      this.regions = Object.values(regions)
    } catch (error) {
      this.hasError = true
    }
  },
  mounted () {
    this.$nextTick(() => {
      // console.log(this.$refs.map)
    })
  }
}
</script>

<style lang="scss" scoped>
  .map-container {
    height: 25rem;
  }
</style>
