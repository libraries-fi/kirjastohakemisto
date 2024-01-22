<template>
  <l-map v-if="center && zoom" :zoom="zoom" :center="center" :bounds="bounds" ref="map">
    <l-tile-layer :url="url" :attribution="attribution"></l-tile-layer>
    <l-polygon v-if="regions" :latLngs="regions"/>
    <l-marker v-for="m in markers" :latLng="m[1]" :title="m[0]" :key="m[0]"/>
  </l-map>
</template>

<script>
import L from 'leaflet'
import { LMap, LTileLayer, LMarker, LPolygon } from 'vue2-leaflet'

/**
 * FIXME: Should read this from Webpack config or something...
 *
 * Intent is to fix resolved paths after they are mangled by Webpack and url-loader/file-loader.
 */
L.Icon.Default.imagePath = process.env.NODE_ENV === 'production' ? '/dist/' : '/dev/'

let iconRetinaUrl = require('leaflet/dist/images/marker-icon-2x.png');
let iconUrl = require('leaflet/dist/images/marker-icon.png');
let shadowUrl = require('leaflet/dist/images/marker-shadow.png');

// Strip the extra /dist/ or /dev/ from the beginning of the image paths
iconRetinaUrl = iconRetinaUrl.replace(L.Icon.Default.imagePath, '');
iconUrl = iconUrl.replace(L.Icon.Default.imagePath, '');
shadowUrl = shadowUrl.replace(L.Icon.Default.imagePath, '');

L.Icon.Default.mergeOptions({
  iconRetinaUrl: iconRetinaUrl,
  iconUrl: iconUrl,
  shadowUrl: shadowUrl
})

export default {
  components: { LMap, LTileLayer, LMarker, LPolygon },
  props: {
    pos: {
      type: Array,
      default: () => [64.96, 27.59]
    },
    zoom: {
      type: Number,
      default: 14
    },
    regions: {
      type: Array,
      default: null
    },
    markers: {
      type: Array,
      default: null
    }
  },
  data: () => ({
    url: 'https://{s}.tile.osm.org/{z}/{x}/{y}.png',
    attribution: 'LOL',
    center: null,
    bounds: null
  }),
  mounted () {
    function center (...points) {
      let [x, y] = [0, 0]

      for (let p of points) {
        x += p[0]
        y += p[1]
      }

      return [x / points.length, y / points.length]
    }

    if (this.regions && this.regions.length > 0) {
      let [a, b, c, d] = [this.regions[0][0], this.regions[0][0], this.regions[0][0], this.regions[0][0]]

      this.regions.forEach((polygon) => {
        polygon.forEach((pos) => {
          if (pos[0] < a[0]) {
            a = pos
          }

          if (pos[1] < b[1]) {
            b = pos
          }

          if (pos[0] > c[0]) {
            c = pos
          }

          if (pos[1] > d[1]) {
            d = pos
          }
        })
      })

      this.bounds = [a, b, c, d]
      this.center = center(a, b, c, d)
    }

    if (!this.center) {
      this.center = this.pos
    }

    this.$nextTick(() => {

    })
  }
}
</script>
