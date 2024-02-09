<template>
  <div>
    <div v-if="library" v-for="contactType in contactInfos" class="contact-info-group">
      <h3 class="contact-info-label mt-3">{{ contactTypeLabel(contactType) }}</h3>

      <table class="table table-sm table-border contact-info-item">
        <thead>
          <tr>
            <th class="w-50">{{ $t('contact-info.contact-detail') }}</th>
            <th class="w-50">{{ $t('contact-info.number-address') }}</th>
          </tr>
        </thead>
        <tbody v-for="entries in contactType.groups">
          <tr>
            <td class="w-50">
              {{ first(entries).name }}
              <template v-if="first(entries).jobTitle"><br/>{{ first(entries).jobTitle }}</template>
              <template v-if="first(entries).responsibility"><br/>{{ first(entries).responsibility }}</template>
            </td>
            <td class="w-50">
              <ul class="list-group list-group-flush">
                <li v-for="entry in entries" class="list-group-item p-0 border-0">
                  <span class="sr-only">{{ entryTypeLabel(entry) }}</span>
                  <a :href="entryLinkValue(entry)">{{ entry.number || entry.email || entry.url }}</a>
                </li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>

    </div>
  </div>
</template>

<script>
import { addToMap, addToMapArray, first, last } from '@/mixins'

function libraryContactInfo (library) {
  const contactInfos = new Map()

  const contactTypes = [
    {name: "phones"},
    {name: "emails"},
    {name: "links"},
    {name: "personnel"}
  ]

  function makeContactTypeEntry (name) {
    return { name, namedGroups: new Map() }
  }

  for (let contactType of contactTypes) {
    let { name } = contactType
    contactInfos.set(name, makeContactTypeEntry(name))
  }

  for (let entry of library.phoneNumbers) {
    entry.type = 'phone'
    let contactType = 'phones'
    addToMapArray(contactInfos.get(contactType).namedGroups, entry.name, entry)
  }

  for (let entry of library.emailAddresses) {
    entry.type = 'email'
    let contactType = 'emails'
    addToMapArray(contactInfos.get(contactType).namedGroups, entry.name, entry)
  }

  for (let entry of library.links) {
    entry.type = 'link'
    let contactType = 'links'
    addToMapArray(contactInfos.get(contactType).namedGroups, entry.name, entry)
  }

  for (let person of library.persons) {
    let contactType = 'personnel'
    const name = `${person.lastName}, ${person.firstName}`

    if (person.email) {
      addToMapArray(contactInfos.get(contactType).namedGroups, name, {
        name,
        jobTitle: person.jobTitle,
        responsibility: person.responsibility,
        email: person.email,
        type: 'email',
        grouptype: 'person'
      })
    }

    if (person.phone) {
      addToMapArray(contactInfos.get(contactType).namedGroups, name, {
        name,
        jobTitle: person.jobTitle,
        responsibility: person.responsibility,
        number: person.phone,
        type: 'phone',
        grouptype: 'person'
      })
    }

  }

  contactInfos.get('personnel').namedGroups = new Map([
    ...contactInfos.get('personnel').namedGroups.entries()].sort((a, b) => {
    return a[0].localeCompare(b[0]);
  }));

  for (let contactType of contactInfos.values()) {
    contactType.groups = [...contactType.namedGroups.values()]

    if (contactType.groups.length < 1) {
      contactInfos.delete(contactType.name)
    }
  }
  return [...contactInfos.values()]
}

export default {
  props: {
    library: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    contactInfos () {
      return libraryContactInfo(this.library)
    }
  },
  methods: {
    first,
    last,
    entryLinkValue (entry) {
      switch (entry.type) {
        case 'phone':
          return `tel:+358${entry.number.replace(/\D/g, '').substr(1)}`

        case 'email':
          return `mailto:${entry.email}`

        case 'link':
          return entry.url
      }
    },
    contactTypeLabel (contactType) {
      switch (contactType.name) {
        case 'phones':
          return this.$t('contact-info.phones')

        case 'emails':
          return this.$t('contact-info.emails')

        case 'links':
          return this.$t('contact-info.links')

        case 'personnel':
          return this.$t('contact-info.personnel')
      }
    },
    entryTypeLabel (entry) {
      switch (entry.type) {
        case 'phone':
          return this.$t('contact-info.phone')

        case 'email':
          return this.$t('contact-info.email')

        case 'link':
          return this.$t('contact-info.link')
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  .contact-info-group {
    margin-bottom: spacing(3);
    border-bottom: $table-border-width solid $table-border-color;
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
