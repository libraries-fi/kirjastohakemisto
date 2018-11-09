<template>
  <div>
    <div v-if="data" v-for="department in data" class="contact-info-group">
      <h3 class="contact-info-label">{{ department.name || $t('contact-info.common') }}</h3>

      <div class="contact-info-body">
        <div v-for="entries in department.groups" class="contact-info-item">
          <h4 class="contact-info-entry-label h6">{{ first(entries).name }}</h4>
          <ul>
            <li v-for="entry in entries">
              <span class="sr-only">{{ entryTypeLabel(entry) }}</span>
              <a :href="entryLinkValue(entry)">{{ entry.number || entry.email || entry.url }}</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { first, last } from '@/mixins'

  export default {
    props: ['data'],
    methods: {
      first, last,
      entryIcon(entry) {
        switch (entry.type) {
          case 'phone':
            return faPhone

          case 'email':
            return faAt

          case 'link':
            return faLink
        }
      },
      entryLinkValue(entry) {
        switch (entry.type) {
          case 'phone':
            return `tel:+358${entry.number.replace(/\D/g, '').substr(1)}`

          case 'email':
            return `mailto:${entry.email}`

          case 'link':
            return entry.url
        }
      },
      entryTypeLabel(entry) {
        switch (entry.type) {
          case 'phone':
            return this.$t('contact-info.phone')

          case 'email':
            return this.$t('contact-info.email')

          case 'link':
            return this.$t('contact-info.link')
        }
      },
    }

  }
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  .contact-info-group {
    margin-bottom: spacing(3);
    border-bottom: $table-border-width solid $table-border-color;

    @include media-breakpoint-up("sm") {
      display: flex;
    }
  }

  .contact-info-group:last-of-type {
    border-bottom-width: 0;
  }

  .contact-info-label {
    flex-basis: 10rem;
  }

  .contact-info-body {
    flex: 1 0 auto;
  }

  .contact-info-entry-label {
    line-height: 1.6;
  }


</style>
