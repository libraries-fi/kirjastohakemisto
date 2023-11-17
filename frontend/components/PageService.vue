<template>
  <main v-if="service">
    <h1 class="page-title">{{ service.name }} <b-badge variant="success">{{ $t(`service-type.${service.type}`)}}</b-badge></h1>

    <div v-if="closeLibraries.length > 0" class="mt-4">
      <h2 class="h5 font-weight-normal font-family-base">
        {{ $t('service.offered-nearby') }} ({{ $t('service.max-distance') }} {{closeLibraryDistance}} km):
      </h2>
      <ul v-for="library of closeLibraries" class="list-unstyled border p-3 mb-1 close-library">
        <h3 class="h4">
          <router-link :to="{name: 're', params: {slug: library.slug}}">{{ library.name }}</router-link><small>
          <span class="text-uppercase font-weight-normal font-family-base">, {{ library.address.city }}</span></small>
          <b-badge class="float-right badge-light" v-if="library.distance">{{ formatDistance(library.distance) }}</b-badge>
        </h3>
        <ul v-for="instance of library.services">
          <li v-if="instance.id == service.id" class="mb-1">
            {{ instance.name || instance.standardName }}
            <div v-if="instance.description" v-html="instance.description"/>
            <div v-if="!instance.description && instance.shortDescription">{{ instance.shortDescription }}</div>
          </li>
        </ul>
      </ul>
    </div>

    <div class="mt-4" v-if="otherLibraries.length > 0">
      <h2 class="h5 font-weight-normal font-family-base">
        <template v-if="closeLibraries.length > 0">{{ $t('service.offered-elsewhere')}}:</template>
        <template v-else>{{ $t('service.offered-at') }}:</template>
      </h2>
      <ul v-for="library of otherLibraries" class="list-unstyled border p-3 mb-1 other-library">
        <h3 class="h4">
          <router-link :to="{name: 're', params: {slug: library.slug}}">{{ library.name }}</router-link><small>
          <span class="text-uppercase font-weight-normal font-family-base">, {{ library.address.city }}</span></small>
          <b-badge class="float-right badge-light" v-if="library.distance">{{ formatDistance(library.distance) }}</b-badge>
        </h3>
        <ul v-for="instance of library.services">
          <li v-if="instance.id == service.id">
            {{ instance.name || instance.standardName }}
          </li>
        </ul>
      </ul>
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
import { coordStr, formatDistance, kirkanta } from '@/mixins'
import { faMap, faSmile, faMeh } from '@fortawesome/free-regular-svg-icons'
import Page404 from './Page404'

const PROXIMITY_THRESHOLD = 25

export default {
  components: { Page404 },
  data: () => ({
    service: null,
    closeLibraries: [],
    otherLibraries: [],
    closeLibraryDistance: PROXIMITY_THRESHOLD,
    faMap,
    hasError: false
  }),
  methods: {
    formatDistance
  },
  async created () {
    try {
      this.service = (await kirkanta.get('service', {
        slug: this.$route.params.service
      })).data

      const params = {
        with: ['services'],
        sort: ['name'],
        service: this.service.id,
        limit: 9999
      }

      try {
        let pos = await this.$location.query(true)

        Object.assign(params, {
          'geo.pos': coordStr(pos.coords)
        })
      } catch (err) {
        // pass
      }

      const libraries = (await kirkanta.search('library', params)).items

      for (let library of libraries) {
        if (library.distance && library.distance < PROXIMITY_THRESHOLD) {
          this.closeLibraries.push(library)
        } else {
          this.otherLibraries.push(library)
        }
      }
    } catch (err) {
      this.hasError = true
    }

    this.closeLibraries.sort((a, b) => a.distance - b.distance)
  }
}
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  h1 > .badge {
    vertical-align: text-top;
  }

  .close-library,
  .other-library {
    border-radius: $border-radius-sm;
  }

  .close-library {
    background-color: white;
    border-radius: $border-radius-sm;
  }

  .badge-light {
    min-width: 3.5rem;
  }
</style>
