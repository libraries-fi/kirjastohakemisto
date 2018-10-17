<template>
  <div id="app">
    <b-navbar toggleable="md" type="dark" variant="dark" sticky>
      <div class="container">
        <b-navbar-toggle target="nav-collapse"/>
        <b-navbar-brand :to="{ name: 'front' }">{{ $t('app.name') }}</b-navbar-brand>
        <b-collapse id="nav-collapse" is-nav>
          <b-navbar-nav>
            <b-nav-item :to="{ name: 'search' }">Search</b-nav-item>
            <b-nav-item :to="{ name: 'library.collection' }">Libraries</b-nav-item>
            <b-nav-item :to="{ name: 'service.collection' }">Services</b-nav-item>
            <b-nav-item :to="{ name: 'info' }">Info</b-nav-item>
          </b-navbar-nav>
        </b-collapse>
        <toggle-button v-model="locationDataAllowed" :sync="true" @change="toggleGeolocation" labels/>
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
      locationDataAllowed: false
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
          } catch (err) {
            this.locationDataAllowed = false
            console.warn('user aborted geolocation')
          }
        }
      }
    }
  }
</script>
