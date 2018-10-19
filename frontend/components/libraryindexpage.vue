<template>
  <main>
    <h1 class="page-title">{{ $t(pageTitle) }}</h1>

    <nav class="my-4">
      <b-nav pills>
        <b-nav-item active>{{ $t('index.by-initial') }}</b-nav-item>
        <b-nav-item>{{ $t('index.by-consortium') }}</b-nav-item>
        <b-nav-item>{{ $t('index.by-municipality') }}</b-nav-item>
      </b-nav>
    </nav>

    <div v-for="group of groups" class="index">
      <h2 :id="group[0]">
        <a :href="`#${group[0]}`">{{ group[0] }}</a>
      </h2>

      <ul class="list-unstyled index-section">
        <li v-for="library of group[1]">
          <router-link :to="{name: 'slug-search', params: {slug: library.slug}}">{{ library.name }}</router-link>
        </li>
      </ul>
    </div>
  </main>
</template>

<script>
  import { kirkanta } from '@/mixins'

  function groupByProperty(items, prop, callback) {
    const groups = new Map;

    for (let item of items) {
      let key = callback ? callback(item[prop]) : item[prop]

      if (!groups.has(key)) {
        groups.set(key, [item])
      } else {
        groups.get(key).push(item)
      }
    }

    return groups
  }

  function toArray(iterable) {
    return [...iterable]
  }

  export default {
    data: () => ({
      pageTitle: 'index.libraries',
      libraries: [],
      groups: []
    }),
    async created() {
      let response = await kirkanta.search('library', {
        limit: 9999
      })

      let groups = groupByProperty(response.items, 'name', (name) => name.substr(0, 1).toUpperCase())

      this.libraries = response.items
      this.groups = toArray(groups)
    }
  }
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  .index-section {
    @include media-breakpoint-up("sm") {
      columns: 2;
    }

    @include media-breakpoint-up("md") {
      columns: 3;
    }

    @include media-breakpoint-up("lg") {
      columns: 4;
    }
  }
</style>
