<template>
  <div @click="closeAll">
    <h2>
      <fa :icon="faStreetView"/>
      {{ $t('library.services') }}
    </h2>

    <div v-for="group of categories">
      <h3>{{ $t(`service-type.${first(group)}`) }}</h3>
      <ul class="list-unstyled services-list">
        <li v-for="(service, i) of last(group)" :id="service.uniqueId + '--item'">
          <a :id="service.uniqueId" class="link" tabindex="0" @keyup.enter="doClick">
            {{ service.name || service.standardName }}
          </a>
          <b-popover title="Test Popover" :container="service.uniqueId + '--item'" :target="service.uniqueId" :data-source="service.uniqueId" placement="bottom">
            <template slot="title">
              <b-btn class="close" @click="close" variant="link" tabindex="0">
                <span aria-hidden="true">&times;</span>
              </b-btn>
              {{ service.name || service.standardName }}
            </template>
            <div v-if="service.description" v-html="service.description"/>
            <ul class="service-links">
              <li>
                <router-link :to="{name: 'rs', params: {slug: service.slug}}">{{ $t('service.common-availability') }}</router-link>
              </li>
              <li v-if="service.website">
                <a :href="service.website" class="external-link">{{ $t('service.website') }}</a>
              </li>
            </ul>
          </b-popover>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
  import Popper from 'popper.js'
  import bPopover from 'bootstrap-vue/es/directives/popover/popover'
  import { faStreetView } from '@fortawesome/free-solid-svg-icons'
  import { addToMapArray, first, last } from '@/mixins'

  export default {
    props: ['services'],
    directives: {
      bPopover
    },
    data: () => ({
      activePopups: [],
      faStreetView
    }),
    computed: {
      categories() {
        const groups = new Map
        const instances = new Map

        for (let service of this.services) {
          if (!instances.has(service.id)) {
            instances.set(service.id, 1)
          } else {
            instances.set(service.id, instances.get(service.id) + 1)
          }

          service.uniqueId = `${service.slug}--${instances.get(service.id)}`
          addToMapArray(groups, service.type, service)
        }

        return [...groups]
      }
    },
    methods: {
      first,
      last,
      close(event) {
        this.$root.$emit('bv::hide::popover')
      },
      closeAll(event) {
        if (event) {
          for (let id of this.activePopups) {
            if (event.target.id != id) {
              this.$root.$emit('bv::hide::popover', id)
            }
          }
        } else {
          this.$root.$emit('bv::hide::popover')
        }
      },
      doClick(event) {
        this.$root.$emit('bv::show::popover', event.target.id)
      }
    },
    mounted() {
      this.$root.$on('bv::popover::shown', (event) => {
        if (!event.target.id) {
          let id = Math.ceil(Math.random() * 999999)
          event.target.id = `popup-toggle-${id}`
        }
        this.activePopups.push(event.target.id)
      })
    }
  }
</script>

<style lang="scss">
  @import "../scss/bootstrap/init";

  .link {
    cursor: pointer;
  }

  .popover-body {
    white-space: pre-line;
  }

  .close {
    display: inline-block;
    margin-left: spacing(2);
    line-height: 0.9;
  }

  .services-list {
    @include media-breakpoint-up("sm") {
      columns: 2;
    }

    @include media-breakpoint-up("md") {
      columns: 4;
    }
  }

  .service-links {
    list-style-position: inside;
    padding-left: spacing(2);
  }
</style>
