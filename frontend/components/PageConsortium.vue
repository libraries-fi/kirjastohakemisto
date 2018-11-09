<template>
  <main v-if="consortium">
    <h1 class="page-title">{{ consortium.name }}</h1>
    <api-image :file="consortium.logo" size="medium"/>
    <a :href="consortium.homepage" class="d-block external-link">{{ $t('consortium.homepage') }}</a>

    <div v-if="regions" class="map-container">
      <map-view ref="map" :regions="regions" :markers="markers"/>
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
</template>

<script>
  import { constants, detectLanguage, kirkanta } from '@/mixins'
  import axios from 'axios'

  import MapView from './MapView'

  const API_BOUNDARY_URL = '/backend/regions'

  export default {
    components: { MapView },
    data: () => ({
      consortium: null,
      cities: [],
      libraries: [],
      regions: null,
      markers: null,
    }),
    async created() {
      const cid = this.$route.params.consortium

      let pConsortiums = kirkanta.get('consortium', {
        slug: cid
      })

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
    },
    mounted() {
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
