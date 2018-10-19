<template>
  <main v-if="consortium">
    <h1 class="page-title">{{ consortium.name }}</h1>
    <api-image :file="consortium.logo" size="medium"/>
  </main>
</template>

<script>
  import { kirkanta } from '@/mixins'

  export default {
    data: () => ({
      consortium: null,
    }),
    async created() {
      let result = await kirkanta.get('consortium', {
        slug: this.$route.params.consortium
      })

      this.consortium = result.data

      console.log(this.consortium)
    }
  }
</script>
