 <template>
   <div class="pt-3">
     <header class="mb-4">
       <h1 class="sr-only">{{ $t('app.name') }}</h1>
       <p class="h1 text-justify mb-4">{{ $t('front.hello') }}</p>
       <router-link :to="{name: 'search'}" class="btn btn-primary">
         <fa :icon="faSearch" class="mr-2"/>
         {{ $t('front.open-search') }}
       </router-link>
     </header>
    <main v-if="this.currentCity">
      <p class="mb-1">{{ $t('library.nearby', {city: this.currentCity}) }}</p>
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
  </div>
</template>

<script>
import kirkanta from '@/mixins/kirkanta'
import { faSearch } from '@fortawesome/free-solid-svg-icons'

export default {
  data: () => ({
    libraries: [],
    currentCity: null,
    faSearch
  }),
  async created () {
    let pos = await this.$location.query()
    let response = await kirkanta.search('library', {
      'geo.pos': `${pos.coords.latitude},${pos.coords.longitude}`,
      limit: 10
    })

    this.libraries = response.items
    this.currentCity = this.libraries.length > 0 ? this.libraries[0].address.city : null
  }
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
