<template>
  <p>searching...</p>
</template>

<script>
  import { kirkanta } from '@/mixins'

  export default {
    async created() {
      let result = await kirkanta.get('consortium', {
        slug: this.$route.params.slug,
      })

      let consortium = result.data

      this.$router.replace({
        name: 'consortium.show',
        params: {
          consortium: consortium.slug,
        }
      })
    }
  }
</script>
