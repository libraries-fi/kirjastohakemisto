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
      let result = await kirkanta.get('consortium', {
        slug: this.$route.params.slug
      }, {}, true)

      let consortium = result.data

      this.$router.replace({
        name: 'consortium.show',
        params: {
          consortium: consortium.slug
        }
      })
    } catch (error) {
      this.hasError = true
    }
  }
}
</script>
