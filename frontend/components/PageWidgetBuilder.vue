<template>
  <main>
      <div id="widget-builder"></div>
  </main>
</template>

<script>
import { detectLanguage } from '@/mixins'
import config from '@/config'

export default {
  mounted() {
    let widgetScript = document.createElement('script')
    widgetScript.setAttribute('src', config.widgetBuilderUrl + '/builder.js')
    document.head.appendChild(widgetScript)
  },
}
</script>
