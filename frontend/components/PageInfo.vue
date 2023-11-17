<template>
  <main>
    <h1 class="page-title">{{ $t('info.page-title') }}</h1>

    <div v-if="isFinnish()">
      <ul>
        <li><a href="https://www.kirjastot.fi/feedback/kirjastohakemisto">Ota yhteyttä</a></li>
        <li><a href="https://www.kirjastot.fi/rekisteriselosteet">Rekisteriseloste</a></li>
        <li><a href="/saavutettavuusseloste">Saavutettavuusseloste</a></li>
        <li><a href="https://hakemisto.kirjastot.fi/open-data">Avoin data ja upokkeet</a></li>
      </ul>
    </div>

    <div v-if="isEnglish()"></div>

    <div v-if="isSwedish()"></div>

  </main>
</template>

<script>
import { detectLanguage } from '@/mixins'

const langcode = detectLanguage()

export default {
  methods: {
    isFinnish () {
      if (langcode === 'fi') {
        return true
      } 
      return false
    },
    isEnglish () {
      if (langcode === 'en') {
        return true
      } 
      return false
    },
    isSwedish () {
      if (langcode === 'sv') {
        return true
      } 
      return false
    }
  }
}
</script>
