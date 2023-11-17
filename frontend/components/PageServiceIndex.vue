<template>
  <main>
    <h1 class="page-title">{{ $t('index.services') }}</h1>

    <div class="index">
      <div v-for="group of groups" class="index-section">
        <a :id="group[0]" class="anchor"/>
        <h2 class="index-section-title border-top pt-2 mt-4">
          <a :href="`#${group[0]}`">{{ group[0] }}</a>
        </h2>

        <ul class="list-unstyled index-section-content">
          <li v-for="service of group[1]">
            <router-link :to="{name: 'rs', params: {slug: service.slug}}">{{ service.name }}</router-link>
          </li>
        </ul>
      </div>
    </div>
  </main>
</template>

<script>
import { toArray, groupBy } from '@/mixins/collections'
import { detectLanguage, kirkanta } from '@/mixins'

export default {
  data: () => ({
    services: [],
    refs: {},
    groups: []
  }),
  methods: {
    buildGroups () {
      const groups = groupBy(this.services, (service) => this.$t(`service-type.${service.type}`))

      this.groups = toArray(groups).sort((a, b) => {
        return a[0].localeCompare(b[0], detectLanguage())
      })
    }
  },
  watch: {
    $route: 'buildGroups'
  },
  async created () {
    let response = await kirkanta.search('service', {
      limit: 9999
    })

    this.services = response.items
    this.refs = response.refs

    this.buildGroups()
  }
}
</script>
