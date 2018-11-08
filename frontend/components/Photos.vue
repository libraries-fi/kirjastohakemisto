<template>
  <div class="photos">
    <api-image :file="currentPhoto.files" size="medium" alt=""/>
    <button type="button" class="expand" @click="expand">
      <fa :icon="faExpand"/>
    </button>
    <button type="button" class="prev" @click="prev">«</button>
    <button type="button" class="next" @click="next">»</button>
  </div>
</template>

<script>
  import { faExpand } from '@fortawesome/free-solid-svg-icons'

  export default {
    props: {
      source: {
        type: Array,
        default: []
      },
      index: {
        type: Number,
        default: 0
      }
    },
    data: () => ({
      currentIndex: 0,
      faExpand
    }),
    computed: {
      currentPhoto() {
        return this.source[this.currentIndex]
      }
    },
    methods: {
      prev() {
        this.currentIndex = Math.max(this.currentIndex - 1, 0)
      },
      next() {
        this.currentIndex = Math.min(this.currentIndex + 1, this.source.length - 1)
      },
      expand() {
        console.log("EXPAND");
      }
    },
    created() {
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
    object-fit: contain;
    object-position: left top;
  }

  button {
    background: none;
    border: 1px solid white;
    position: absolute;
    width: 2rem;
    height: 2rem;
    color: white;
    font-weight: bold;
    cursor: pointer;
  }

  .expand {
    top: 0;
    right: 0;
  }

  .prev {
    left: 0;
    top: 50%;
  }

  .next {
    right: 0;
    top: 50%;
  }
</style>
