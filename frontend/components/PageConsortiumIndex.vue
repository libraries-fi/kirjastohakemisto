<template>
  <main>
    <h1 class="page-title">{{ $t('index.consortiums') }}</h1>

    <div class="index">
      <div v-for="group of groups" class="index-section">
        <a :id="group[0]" class="anchor"/>
        <h2 class="index-section-title">
          <a :href="`#${group[0]}`">{{ group[0] }}</a>
        </h2>

        <ul class="list-unstyled index-section-content">
          <li v-for="library of group[1]">
            <router-link :to="{name: 'rc', params: {slug: library.slug}}">{{ library.name }}</router-link>
          </li>
        </ul>
      </div>
    </div>
  </main>
</template>

<script>
import { toArray, groupBy } from '@/mixins/collections'
import { kirkanta, initial, detectLanguage } from '@/mixins'
function indexByInitial (library) {
  return initial(library.name)
}

export default {
  data: () => ({
    consortiums: [],
    refs: {},
    groups: []
  }),
  methods: {
    buildGroups () {
      this.groups = toArray(groupBy(this.consortiums, indexByInitial)).sort((a, b) => {
        return a[0].localeCompare(b[0], detectLanguage())
      })
    }
  },
  watch: {
    $route: 'buildGroups'
  },
  async created () {
    let response = await kirkanta.search('consortium', {
      limit: 9999
    })

    this.consortiums = response.items
    this.refs = response.refs

    this.buildGroups()
  }
}
</script>

<style lang="scss">

</style>
