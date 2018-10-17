<template>
  <img v-if="hasImage()" :src="imageUrl"/>
  <fa v-else :icon="faCamera" size="6x"/>
</template>

<script>
  import { useBetaKirkantaDomain } from '@/mixins/beta-compat'
  import { faCamera } from '@fortawesome/free-solid-svg-icons'

  export default {
    props: ['file', 'size'],
    data: () => ({
      faCamera
    }),
    methods: {
      hasImage() {
        return this.file && this.file[this.imageSize]
      }
    },
    computed: {
      imageSize: function() {
        return this.size || 'small'
      },
      imageUrl: function() {
        return useBetaKirkantaDomain(this.file[this.imageSize].url)
      },
    }
  }
</script>
