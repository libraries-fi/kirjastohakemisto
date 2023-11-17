<template>
  <main>
    <div v-if="hasError">
      <Page404/>
    </div>
    <div v-else>
      <div>
        <p class="mt-4 text-center">{{ $t('app.searching') }}...</p>
      </div>
      <div class="d-flex justify-content-center my-5">
        <div id="page-load-throbber" class="loader" aria-hidden="true"></div>
      </div>
    </div>
  </main>
</template>

<script>
import { kirkanta } from '@/mixins'

import Page404 from '@/components/Page404'

export default {
  components: { Page404 },
  data() {
    return {
      hasError: false
    }
  },
  async created () {
    try {
      let result = await kirkanta.get('library', {
        slug: this.$route.params.slug,
        refs: ['city']
      }, {}, true)

      let library = result.data

      this.$router.replace({
        name: 'library.show',
        params: {
          library: library.slug,
          city: result.refs.city[library.city].slug
        }
      })

    } catch (error) {
      this.hasError = true
    }
  }
}
</script>

<style lang="scss">
  #page-load-throbber.loader {
    font-size: .5rem;
    -webkit-transform: translateZ(0);
    -ms-transform: translateZ(0);
    transform: translateZ(0) scale(1, 1);
  }
</style>
