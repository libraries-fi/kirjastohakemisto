<template>
  <div id="app" @click="closeStuff">
    <b-navbar toggleable="md" type="dark" variant="dark" sticky id="nav-main">
      <div class="container">
        <b-navbar-toggle target="nav-collapse"/>
        <b-navbar-brand :to="{ name: 'front' }">{{ $t('app.name') }}</b-navbar-brand>
        <b-collapse id="nav-collapse" is-nav>
          <b-navbar-nav>
            <b-nav-item :to="{ name: 'search' }">{{ $t('nav.search')}}</b-nav-item>
            <b-nav-item :to="{ name: 'library.collection' }">{{ $t('nav.libraries')}}</b-nav-item>
            <b-nav-item :to="{ name: 'consortium.collection' }">{{ $t('nav.consortiums') }}</b-nav-item>
            <b-nav-item :to="{ name: 'service.collection' }">{{ $t('nav.services') }}</b-nav-item>
            <b-nav-item :to="{ name: 'info' }">{{ $t('nav.info') }}</b-nav-item>
          </b-navbar-nav>
        </b-collapse>
        <toggle-button v-model="locationDataAllowed" :sync="true" @change="toggleGeolocation" labels :title="locationPosition"/>
      </div>
    </b-navbar>
    <div class="container">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
  import { geolocation } from './mixins'

  export default {
    name: "app",
    data: () => ({
      locationDataAllowed: false,
      locationPosition: null
    }),
    created() {
      geolocation.test()
        .then(() => {
          this.locationDataAllowed = true
        })
        .catch(() => {
          this.locationDataAllowed = false
        })
    },
    methods: {
      async toggleGeolocation(event) {
        if (event.value) {
          try {
            let pos = await geolocation.gps()
            console.log('GOT POS', pos)

            this.locationPosition = `${pos.coords.latitude}, ${pos.coords.longitude}`
          } catch (err) {
            this.locationDataAllowed = false
            console.warn('user aborted geolocation')
          }
        }
      },
      closeStuff(event) {
        if (event.target.tagName != 'A') {
          this.$root.$emit('bv::hide::popover')
        }
      }
    }
  }
</script>
