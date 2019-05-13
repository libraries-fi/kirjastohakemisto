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
      <div v-for="(group, index) in footer.links" class="col-lg link-group" :data-kfoot-open="expandedMenu == index">
        <h3 @click="toggleMenu(index)">{{ group.title }}</h3>
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

const GFX_BASE_URL = 'https://gfx.kirjastot.fi/shared-footer/'

export default {
  data: () => ({
    expandedMenu: null,
    footer: {
      title: null,
      logo: {},
      logo_public_libraries: {},
      menu_button: {},
      cookies: {},
      links: []
    }
  }),
  methods: {
    toggleMenu (index) {
      if (this.expandedMenu === index) {
        this.expandedMenu = null
      } else {
        this.expandedMenu = index
      }
    }
  },
  async created () {
    const lang = 'fi'

    try {
      const response = await axios.get(GFX_BASE_URL, {
        params: { lang },
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })

      this.footer = response.data
    } catch (error) {
      // Network error or something really, really bad.
    }
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

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  $group-bg: rgba(255, 255, 255, 0.3);

  #kifi-footer {
    background: $footer-bg;
    display: flex;
    flex-flow: column;

    a {
      color: inherit;
      font-size: $font-size-sm;
    }

    #footer-logo {
      display: flex;
    }

    @include media-breakpoint-down("md") {
      #footer-logo {
        justify-content: space-around;
      }

      #footer-logo {
        .public-library-logo {
          display: none;
        }
      }

      .link-group {
        position: relative;

        ul {
          display: none;
        }

        button {
          border-width: 0;
          position: absolute;
          width: 100%;
          display: block;
          left: 0;
          top: 0;
          opacity: 0;

          // background: red;
        }
      }

      .link-group:hover {
        h3 {
          opacity: .7;
          cursor: pointer;
        }
      }

      [data-kfoot-open] {
        background: $group-bg;

        h3 {
          opacity: unset !important;
        }

        ul {
          display: unset;
        }
      }

      [data-kfoot-toggle][aria-expanded="false"] {
        cursor: pointer;
      }
    }

    @include media-breakpoint-up("lg") {
      #footer-logo {
        justify-content: space-between;

        img:nth-child(1) {
          margin-left: -(map-get($spacers, 2));
        }
      }

      #footer-rest {
        .public-library-logo {
          display: none;
        }
      }
      .link-group {
        text-align: left;

        button {
          display: none;
        }
      }
    }
  }

  #footer-links {
    flex-grow: 1;
    line-height: 1.125;
  }

  #footer-rest {
    img {
      max-width: 250px;
    }

    a {
      padding: 5px 8px;
    }
  }

  #footer-cookies {
    a {
      background-color: white;
    }
  }
</style>
