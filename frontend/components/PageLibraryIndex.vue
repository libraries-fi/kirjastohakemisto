<template>
  <main>
    <h1 class="page-title">{{ $t('index.libraries') }}</h1>

    <nav class="mt-4">
      <b-nav tabs>
        <b-nav-item :to="{name: 'library.collection'}">{{ $t('index.by-initial') }}</b-nav-item>
        <b-nav-item :to="{name: 'library.collection.by-municipality'}">{{ $t('index.by-municipality') }}</b-nav-item>
        <b-nav-item :to="{name: 'library.collection.by-consortium'}">{{ $t('index.by-consortium') }}</b-nav-item>
      </b-nav>
    </nav>

    <div class="border border-top-0 p-3 mb-3">
      <b-form class="p-2 mb-2">
        <span class="sr-only">{{ $t('index.type-filter') }}</span>
        <b-form-radio-group id="type" v-model="form.type" :options="libraryTypeOptions"/>
      </b-form>

      <p class="d-block mb-3 text-muted">{{ $t('index.library-count', {count: visibleCount}) }}</p>

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
    </div>
  </main>
</template>

<script>
  import { kirkanta } from '@/mixins'
  import { filtered, toArray, groupBy } from '@/mixins/collections'
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

  const libraryTypeMap = new Map([
    ['_all', 'index.all-libraries'],
    ['library', 'index.municipal-libraries'],
    ['mobile', 'index.mobile-libraries'],
  ])

  export default {
    data: () => ({
      libraries: [],
      refs: {},
      groups: [],
      visibleCount: 0,
      libraryTypeOptions: {},
      form: {
        type: '_all',
      }
    }),
    methods: {
      buildGroups() {
        this.visibleCount = 0

        const libraries = filtered(this.libraries, (library) => {
          if (this.form.type == '_all' || this.form.type == library.type) {
            this.visibleCount++
            return true
          } else {
            return false
          }
        })

        const groups = (mode) => {
          switch (mode) {
            case 'municipality':
              return groupBy(libraries, (library) => indexByMunicipality(library, this.refs.city))

            case 'consortium':
              let groups = groupBy(libraries, (library) => indexByConsortium(library, this.refs.consortium))
              groups.delete('')
              return groups

            default:
              return groupBy(libraries, (library) => indexByInitial(library))
          }
        }

        this.groups = toArray(groups(this.$route.meta.indexBy)).sort((a, b) => {
          return a[0].localeCompare(b[0], detectLanguage())
        })
      }
    },
    watch: {
      $route: 'buildGroups',
      'form.type': 'buildGroups',
    },
    async created() {
      for (let [value, label] of libraryTypeMap) {
        this.libraryTypeOptions[value] = this.$t(label)
      }

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
