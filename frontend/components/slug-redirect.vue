<template>
  <p>searching...</p>
</template>

<script>
  import { kirkanta } from '@/mixins'

  export default {
    async created() {
      try {
        let result = await kirkanta.get('library', {
          slug: this.$route.params.slug,
          refs: ['city']
        })

        let library = result.data

        this.$router.replace({
          name: 'library.show',
          params: {
            limit: 1,
            library: library.slug,
            city: result.refs.city[library.city].slug
          }
        })
      } catch (error) {
        console.error(error.stack)
        let result = await kirkanta.get('city', {
          slug: this.$route.params.slug
        })

        console.log('city', result.data)
      }
    }
  }
</script>
