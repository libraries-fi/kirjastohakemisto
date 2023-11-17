<template>
  <div id="kifi-footer" class="ml-3 py-4">
  <div class="container bg-light">
    <div class="row">
      <div class="col-md-6 col-xl-7">
        <p class="pr-3"><span class="text-uppercase"><strong>{{ $t('app.name') }}</strong></span> {{ $t('app.info') }}</p>
      </div>
      <nav id="nav-additional" class="navbar align-items-start navbar-light bg-light mt-2 p-0 col-md-6 col-xl-5">
        <b-navbar-nav class="footer-nav justify-content-between d-block d-sm-flex flex-row flex-wrap w-100 px-3 px-xs-0">

          <li v-if="isFinnish()" class="nav-item border-bottom mb-1">
            <a href="https://www.kirjastot.fi/feedback/kirjastohakemisto" class="nav-link">Ota yhteyttä</a>
          <li v-else-if="isSwedish()" class="nav-item border-bottom mb-1">
            <a href="https://www.biblioteken.fi/feedback/kirjastohakemisto" class="nav-link">Ta kontakt</a>
          <li v-else class="nav-item border-bottom mb-1">
            <a href="https://www.libraries.fi/feedback/kirjastohakemisto" class="nav-link">Contact</a>
          </li>

          <li v-if="isFinnish()" class="nav-item border-bottom mb-1">
            <a href="https://www.kirjastot.fi/rekisteriselosteet" class="nav-link">Rekisteriseloste</a>
          <li v-else-if="isSwedish()" class="nav-item border-bottom mb-1">
            <a href="https://www.biblioteken.fi/registerbeskrivningar" class="nav-link">Registerbeskrivning</a>
          </li>

          <li v-if="isFinnish()" class="nav-item border-bottom mb-1">
            <a href="/saavutettavuusseloste" class="nav-link">Saavutettavuusseloste</a>
          <li v-else-if="isSwedish()" class="nav-item border-bottom mb-1">
            <a href="/tillganglighetsutlatande" class="nav-link">Tillgänglighetsutlåtande</a>
          <li v-else class="nav-item border-bottom mb-1">
            <a href="/accessibility-statement" class="nav-link">Accessibility Statement</a>
          </li>

          <li v-if="isFinnish()" class="nav-item border-bottom mb-1">
            <a href="https://hakemisto.kirjastot.fi/open-data" class="nav-link">Avoin data ja upokkeet</a>
          <li v-else-if="isSwedish()" class="nav-item border-bottom mb-1">
            <a href="https://registret.biblioteken.fi/open-data" class="nav-link">Öppen data och gränssnittskomponenter</a>
          <li v-else class="nav-item border-bottom mb-1">
            <a href="https://directory.libraries.fi/open-data" class="nav-link">Open data and widgets</a>
          </li>

        </b-navbar-nav>
      </nav>
    </div>
  </div>
  </div>
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

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  .footer-nav {
    .nav-item {
      border-color: #343a40 !important;

      &:nth-child(1) {
        border-top: 1px solid #343a40 !important;
      }

      @include media-breakpoint-up("sm") {
        width: 47.5%;

        &:nth-child(2) {
          border-top: 1px solid #343a40 !important;
        }
      }
    }
  }
</style>
