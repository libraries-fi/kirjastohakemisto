<template>
  <span></span>
</template>

<script>
export default {
  props: {
    enabled: {
      type: Boolean,
      default: true
    }
  },
  methods: {
    onScroll () {
      let bbox = this.$el.getBoundingClientRect()
      let visible = bbox.top >= 0 && bbox.bottom <= window.innerHeight

      if (this.enabled && visible) {
        this.$emit('scroll')
      }
    }
  },
  created () {
    window.addEventListener('scroll', this.onScroll)
  },
  destroyed () {
    window.removeEventListener('scroll', this.onScroll)
  }
}
</script>
