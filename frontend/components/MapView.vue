<template>
  <l-map :zoom="zoom" :center="pos">
    <l-tile-layer :url="url" :attribution="attribution"></l-tile-layer>
    <l-marker :lat-lng="pos"></l-marker>
  </l-map>
</template>

<script>
  import { L, LMap, LTileLayer, LMarker } from 'vue2-leaflet'

  export default {
    components: { LMap, LTileLayer, LMarker },
    props: {
      pos: {
        type: Object,
        default: {
          lat: 64.96,
          lon: 27.59
        }
      }
    },
    data: () => ({
      zoom: 14,
      url: 'http://{s}.tile.osm.org/{z}/{x}/{y}.png',
      attribution: 'LOL',
    }),
    mounted() {
      this.$nextTick(() => {
        // console.log('TICK')
      })
    }
  }
</script>
