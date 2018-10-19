<template>
  <main class="pt-3">
    <div class="mb-4">
      <p class="h1 text-justify mb-4">{{ $t('front.hello') }}</p>
      <router-link :to="{name: 'search'}" class="btn btn-primary">{{ $t('front.open-search') }}</router-link>
    </div>
    <div class="showcase">
      <template v-for="library in libraries">
        <div class="card">
          <div class="card-img-frame">
            <api-image :file="library.coverPhoto" class="card-img-top" alt=""/>
          </div>
          <div class="card-body">
            <router-link :to="{ name: 're', params: { slug: library.slug }}" class="card-title">
              {{ library.name }}
            </router-link>
          </div>
        </div>
      </template>
    </div>
  </main>
</template>

<script>
  import { geolocation } from '@/mixins/geolocation'
  import kirkanta from '@/mixins/kirkanta'

  export default {
    data: () => ({
      libraries: []
    }),
    async created() {
      let pos = await geolocation.getPosition()
      let response = await kirkanta.search('library', {
        'geo.pos': `${pos.coords.latitude},${pos.coords.longitude}`,
        'geo.dist': 40,
      })

      this.libraries = response.items
    },
  }
</script>

<style lang="scss">
  @import "../scss/bootstrap/init";

  @include media-breakpoint-down("sm") {
    .card {
      flex-basis: 48%;
    }
  }

  @include media-breakpoint-only("md") {
    .card {
      flex-basis: 31%;
    }

    .card:nth-child(10) {
      display: none;
    }
  }

  @include media-breakpoint-up("lg") {
    .card {
      flex-basis: 23%;
    }

    .card:nth-child(9),
    .card:nth-child(10) {
      display: none;
    }
  }

  .showcase {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }

  .card {
    margin-bottom: spacing(3);
  }

  .card-img-frame {
    height: 160px;
    text-align: center;
    overflow: hidden;
  }

  .card-img-top {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }

  .card-body {
    display: flex;
    justify-content: center;
  }

  .card-title {
    text-align: center;
    margin-bottom: 0 !important;
  }
</style>
