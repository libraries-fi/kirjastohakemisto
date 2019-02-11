<template>
  <div>
    <div v-if="library" v-for="department in departments" class="contact-info-group">
      <h3 class="contact-info-label">{{ department.name || $t('contact-info.common') }}</h3>

      <div class="contact-info-body">
        <div v-for="entries in department.groups" class="contact-info-item">
          <h4 class="contact-info-entry-label h6">{{ first(entries).name }}<template v-if="first(entries).info">, {{ first(entries).info }}</template></h4>
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
  import { addToMap, addToMapArray, first, last } from '@/mixins'

  function departmentContactInfo (library) {
    const departments = new Map([
      [null, {
        name: '',
        phones: [],
        emails: [],
        links: [],
        namedGroups: new Map,
      }]
    ])

    const nameMap = new Map([[null, departments.get(null)]])

    function makeDepartmentEntry(name, id, description) {
      return {name, id, description, phones:[], emails: [], links: [], namedGroups: new Map}
    }

    for (let department of library.departments) {
      let { name, id, description } = department
      departments.set(id, makeDepartmentEntry(name, id, description))
      nameMap.set(name, departments.get(id))
    }

    for (let entry of library.phoneNumbers) {
      entry.type = 'phone'
      departments.get(entry.department).phones.push(entry)
      addToMapArray(departments.get(entry.department).namedGroups, entry.name, entry)
    }

    for (let entry of library.emailAddresses) {
      entry.type = 'email'
      departments.get(entry.department).emails.push(entry)
      addToMapArray(departments.get(entry.department).namedGroups, entry.name, entry)
    }

    for (let entry of library.links) {
      entry.type = 'link'
      departments.get(entry.department).links.push(entry)
      addToMapArray(departments.get(entry.department).namedGroups, entry.name, entry)
    }

    for (let person of library.persons) {
      let department = nameMap.get(person.responsibility || null)

      if (!department) {
        department = makeDepartmentEntry(person.responsibility)
        addToMap(departments, person.responsibility, department)
      }

      const name = `${person.firstName} ${person.lastName}`

      if (person.phone) {
        addToMapArray(department.namedGroups, name, {
          name,
          info: (person.jobTitle || '').toLowerCase(),
          number: person.phone,
          type: 'phone',
        })
      }

      if (person.email) {
        addToMapArray(department.namedGroups, name, {
          name,
          info: (person.jobTitle || '').toLowerCase(),
          email: person.email,
          type: 'email',
        })
      }
    }

    for (let department of departments.values()) {
      department.groups = [...department.namedGroups.values()]

      if (!department.groups.length) {
        departments.delete(department.id)
      }
    }

    return [...departments.values()].sort((a, b) => {
      if (!a.name.length) {
        return -1000
      }
      if (!b.name.length) {
        return 1000
      }
      return a.name.localeCompare(b.name)
    })
  }

  export default {
    props: {
      library: {
        type: Object,
        default: () => new Object()
      }
    },
    computed: {
      departments() {
        return departmentContactInfo(this.library)
      }
    },
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
