<template>
  <main v-if="service">
    <h1 class="page-title">{{ service.name }} <b-badge variant="success">{{ $t(`service-type.${service.type}`)}}</b-badge></h1>

    <div v-if="closeLibraries.length > 0" class="mb-4">
      <h2>
        <fa :icon="faSmile"/>
        {{ $t('service.offered-nearby') }}
      </h2>
      <ul v-for="library of closeLibraries" class="list-unstyled border p-3 m-3 close-library">
        <h3>{{ library.name }} <b-badge class="float-right badge-distance" v-if="library.distance">{{ formatDistance(library.distance) }}</b-badge></h3>
        <ul v-for="instance of library.services">
          <li v-if="instance.id == service.id" class="mb-1">
            <router-link :to="{name: 're', params: {slug: library.slug}}">{{ instance.name || instance.standardName }}</router-link>
            <div v-if="instance.description" v-html="instance.description"/>
            <div v-if="!instance.description && instance.shortDescription">{{ instance.shortDescription }}</div>
          </li>
        </ul>
      </ul>
    </div>

    <div v-if="otherLibraries.length > 0">
      <h2>
        <fa :icon="faMeh"/>
        <template v-if="closeLibraries.length > 0">{{ $t('service.offered-elsewhere')}}</template>
        <template v-else>{{ $t('service.offered-at') }}</template>
      </h2>
      <ul v-for="library of otherLibraries" class="list-unstyled border p-3 m-3 other-library">
        <h3>
          {{ library.name }},
          <span class="text-uppercase">{{ library.address.city }}</span>
          <b-badge class="float-right badge-distance" v-if="library.distance">{{ formatDistance(library.distance) }}</b-badge>
        </h3>
        <ul v-for="instance of library.services">
          <li v-if="instance.id == service.id">
            <router-link :to="{name: 're', params: {slug: library.slug}}">{{ instance.name || instance.standardName }}</router-link>
          </li>
        </ul>
      </ul>
    </div>
  </main>
</template>

<script>
import { coordStr, formatDistance, kirkanta } from '@/mixins'
import { faMap, faSmile, faMeh } from '@fortawesome/free-regular-svg-icons'

const PROXIMITY_THRESHOLD = 200

export default {
  data: () => ({
    service: null,
    closeLibraries: [],
    otherLibraries: [],
    faMap,
    faSmile,
    faMeh
  }),
  methods: {
    formatDistance
  },
  async created () {
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

    this.closeLibraries.sort((a, b) => a.distance - b.distance)
  }
}
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  .close-library,
  .other-library {
    border-radius: $border-radius-sm;
  }

  .close-library {
    background-color: rgba(220, 220, 220, 0.3);
    border-radius: $border-radius-sm;

    &:nth-of-type(1) {
      background-color: rgba(126, 207, 228, 0.3);
    }

    &:nth-of-type(2) {
      background-color: rgba(237, 195, 250, 0.3);
    }
  }

  .badge-distance {
    min-width: 3.5rem;
  }
</style>
