<template>
  <main v-if="consortium">
    <h1 class="page-title">{{ consortium.name }}</h1>
    <api-image :file="consortium.logo" size="medium"/>
    <a :href="consortium.homepage" class="d-block external-link">{{ $t('consortium.homepage') }}</a>

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
  import { constants, kirkanta } from '@/mixins'

  export default {
    data: () => ({
      consortium: null,
      cities: [],
      libraries: [],
    }),
    async created() {
      const cid = this.$route.params.consortium

      let pConsortiums = kirkanta.get('consortium', {
        slug: cid
      })

      let pCities = kirkanta.search('city', {
        'consortium.slug': cid,
        limit: constants.AllResults,
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

      console.log(this.cities)
    }
  }
</script>
