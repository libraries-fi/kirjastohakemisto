<template>
  <main>
    <h1 class="page-title">{{ $t('index.libraries') }}</h1>

    <nav class="my-4">
      <b-nav pills>
        <b-nav-item :to="{name: 'library.collection'}">{{ $t('index.by-initial') }}</b-nav-item>
        <b-nav-item :to="{name: 'library.collection.by-municipality'}">{{ $t('index.by-municipality') }}</b-nav-item>
        <b-nav-item :to="{name: 'library.collection.by-consortium'}">{{ $t('index.by-consortium') }}</b-nav-item>
      </b-nav>
    </nav>

    <div class="index">
      <div v-for="group of groups" class="index-section">
        <a :id="group[0]" class="anchor"/>
        <h2 class="index-section-title">
          <a :href="`#${group[0]}`">{{ group[0] }}</a>
        </h2>

        <ul class="list-unstyled index-section-content">
          <li v-for="library of group[1]">
            <router-link :to="{name: 're', params: {slug: library.slug}}">{{ library.name }}</router-link>
          </li>
        </ul>
      </div>
    </div>
  </main>
</template>

<script>
  import { kirkanta } from '@/mixins'
  import { toArray, groupBy } from '@/mixins/collections'
  import { initial, detectLanguage } from '@/mixins'

  function indexByMunicipality(library, refs) {
    return library.address.city
  }

  function indexByConsortium(library, refs) {
    let consortium = refs[library.consortium]
    return consortium ? consortium.name : ''
  }

  function indexByInitial(library) {
    return initial(library.name)
  }

  export default {
    data: () => ({
      libraries: [],
      refs: {},
      groups: [],
    }),
    methods: {
      buildGroups() {
        const groups = (mode) => {
          switch (mode) {
            case 'municipality':
              return groupBy(this.libraries, (library) => indexByMunicipality(library, this.refs.city))

            case 'consortium':
              let groups = groupBy(this.libraries, (library) => indexByConsortium(library, this.refs.consortium))
              groups.delete('')
              return groups

            default:
              return groupBy(this.libraries, (library) => indexByInitial(library))
          }
        }

        this.groups = toArray(groups(this.$route.meta.indexBy)).sort((a, b) => {
          return a[0].localeCompare(b[0], detectLanguage())
        })
      }
    },
    watch: {
      $route: 'buildGroups'
    },
    async created() {
      let response = await kirkanta.search('library', {
        limit: 9999,
        refs: ['consortium']
      })

      this.libraries = response.items
      this.refs = response.refs

      this.buildGroups()
    },
  }
</script>

<style lang="scss">

</style>
