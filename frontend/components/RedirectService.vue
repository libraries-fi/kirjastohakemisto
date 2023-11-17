<template>
  <p>{{ $t('app.searching') }}...</p>
</template>

<script>
import { kirkanta } from '@/mixins'

export default {
  async created () {
    let result = await kirkanta.get('service', {
      slug: this.$route.params.slug
    })

    let service = result.data

    this.$router.replace({
      name: 'service.show',
      params: {
        service: service.slug
      }
    })
  }
}
</script>
