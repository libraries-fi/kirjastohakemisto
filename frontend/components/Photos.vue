<template>
  <div class="photos">
    <api-image v-if="currentPhoto" :file="currentPhoto.files" size="medium" alt=""/>
    <button v-if="hasPrevPhoto" type="button" class="prev" @click="prev"><fa :icon="faPhotoPrev"/><span class="sr-only">{{ $t('photos.prev-photo') }}</span></button>
    <button v-if="hasNextPhoto" type="button" class="next" @click="next"><fa :icon="faPhotoNext"/><span class="sr-only">{{ $t('photos.next-photo') }}{</span></button>
    <p class="picture-caption position-absolute bg-dark text-white m-0 px-2"><small>{{ currentPhoto.description }}</small></p>
  </div>
</template>

<script>
import { faAngleLeft, faAngleRight } from '@fortawesome/free-solid-svg-icons'

export default {
  props: {
    source: {
      type: Array,
      default: () => []
    },
    index: {
      type: Number,
      default: 0
    }
  },
  data: () => ({
    currentIndex: 0
  }),
  computed: {
    hasPrevPhoto () {
      if (this.source.length > 1 && this.currentIndex > 0) {
        return true
      }
      return false
    },
    hasNextPhoto () {
      if (this.source.length > 1 && this.currentIndex < this.source.length - 1) {
        return true
      }
      return false
    },
    currentPhoto () {
      return this.source[this.currentIndex]
    },
    faPhotoPrev: () => faAngleLeft,
    faPhotoNext: () => faAngleRight
  },
  methods: {
    prev () {
      this.currentIndex = Math.max(this.currentIndex - 1, 0)
    },
    next () {
      this.currentIndex = Math.min(this.currentIndex + 1, this.source.length - 1)
    }
  },
  created () {
    this.currentIndex = this.index
  }
}
</script>

<style lang="scss" scoped>
  .photos {
    display: flex;
    flex-flow: column;
    position: relative;
  }

  img {
    object-fit: cover;
    object-position: center;
    max-width: 100%;
    max-height: 100%;
  }

  button {
    background: white;
    border: 1px solid #dee2e6;
    position: absolute;
    width: 2rem;
    height: 2rem;
    color: #212529;
    font-weight: bold;
    cursor: pointer;
  }

  .picture-caption {
    bottom: 0;
  }

  .expand {
    top: 0;
    right: 0;
  }

  .prev {
    left: 0;
    top: 45%;
  }

  .next {
    right: 0;
    top: 45%;
  }
</style>
