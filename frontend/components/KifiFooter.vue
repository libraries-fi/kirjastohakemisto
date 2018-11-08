<template>
  <div class="container text-center text-lg-left" id="kifi-footer">
    <h2 class="sr-only">{{ footer.title }}</h2>
    <div id="footer-logo">
      <a :href="footer.logo.link" :aria-label="footer.logo.label">
        <img :src="footer.logo.file" alt="" height="45"/>
      </a>
      <img :src="footer.logo_public_libraries.file" height="45" class="public-library-logo" :alt="footer.logo_public_libraries.label"/>
    </div>
    <div class="row">
      <div v-for="(group, index) in footer.links" class="col-lg link-group">
        <h3>{{ group.title }}</h3>
        <ul class="list-unstyled">
          <li v-for="link in group.links">
            <a :href="link.url" class="footer-link">{{ link.label }}</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios'

  const GFX_BASE_URL = 'https://gfx.kirjastot.fi/shared-footer/';

  export default {
    data: () => ({
      expandedMenu: null,
      footer: {
        title: null,
        logo: {},
        logo_public_libraries: {},
        menu_button: {},
        cookies: {},
        links: [],
      }
    }),
    async created() {
      const lang = 'fi'

      const response = await axios.get(GFX_BASE_URL, {
        params: { lang },
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })

      this.footer = response.data

      console.log(response.data)
    }
  }
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  #kifi-footer {
    // background-color: #dedcdc;
    // color: black;
    font-family: Roboto, sans-serif;
    padding: spacing(3);
  }

  #footer-logo {
    margin-bottom: spacing(3);
    display: flex;
    justify-content: space-between;
  }

  .footer-link {
    color: inherit;
    font-size: 0.875rem;
    font-weight: 300;
  }
</style>
