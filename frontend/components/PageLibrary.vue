<template>
  <main v-if="library" class="pt-3">
    <div class="visual-section">
      <h1>{{ library.name }}</h1>
      <blockquote v-if="library.slogan">
        <fa :icon="faQuoteRight" aria-hidden="true"/>
        {{ library.slogan }}
      </blockquote>
    </div>

    <div class="row visual-section">
      <div class="col-md-6 cover-photo-frame">
        <api-image :file="library.coverPhoto" size="medium" alt="" class="cover-photo"/>
      </div>
      <div class="col-md-6">
        <h3 class="text-center">{{ $t('calendar.week', {week: 4}) }}</h3>
        <table class="table table-sm">
          <thead class="sr-only">
            <tr>
              <th>{{ $t('calendar.date') }}</th>
              <th>{{ $t('calendar.week-day') }}</th>
              <th>{{ $t('calendar.schedules') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="entry of library.schedules.slice(0, 7)">
              <td>{{ entry.date }}</td>
              <td>day name</td>
              <td v-if="entry.closed">{{ $t('calendar.closed') }}</td>
              <td v-else>
                {{ first(entry.times).from }} – {{ last(entry.times).to }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-html="library.description" class="text-justify visual-section"/>

    <section v-if="library.links" class="info-links visual-section">
      <h2 class="sr-only">{{ $t("library.other-links") }}</h2>
      <a v-for="link in sortedLinks" :href="link.url" class="info-link">
        <fa v-if="linkIcon(link)" :icon="linkIcon(link)"/>
        {{ link.name }}
      </a>
    </section>

    <section v-if="library.address" class="row visual-section">
      <h2 class="sr-only">{{ $t("Contact details") }}</h2>

      <div class="col-md-6">
        <h3>{{ $t('library.location') }}</h3>
        <address>
          <p>
            {{ library.address.street }}, {{ library.address.zipcode }} {{ library.address.city }} <template v-if="library.address.area">({{ library.address.area }})</template><br/>
            <template v-if="library.address.info">{{ library.address.info }}</template>
          </p>

          <p v-if="library.email">
            <b>{{ library.email.name }}</b><br/>
            <a :href="'mailto:' + library.email.email">{{ library.email.email }}</a><br/>
          </p>

          <p v-if="library.phone">
            <b>{{ library.phone.name }}</b><br/>
            <a :href="'tel:+358' + library.phone.number.replace(/\D/g, '').substr(1)">{{ library.phone.number }}</a>
          </p>
        </address>
      </div>

      <div class="col-md-6">
        <!-- <fa :icon="faEnvelope" size="3x"/> -->
        <div v-if="library.mailAddress">
          <h3>{{ $t('library.location-mail') }}</h3>
          <p>
            {{ library.name }}<br/>
            <template v-if="library.mailAddress.street">{{ library.mailAddress.street }}<br/></template>
            <template v-if="library.mailAddress.box_number">P.O. Box {{ library.mailAddress.box_number}}<br/></template>
            <template>{{ library.mailAddress.zipcode }} {{ library.mailAddress.area.toUpperCase() }}<br/></template>
          </p>
        </div>
      </div>
    </section>

    <section v-if="hasPublicTransportation()">
      <h2 class="sr-only">{{ $t("Transit directions") }}</h2>
      <h3>{{ $t("Public transportation") }}</h3>

      <div class="row">
        <div v-if="library.transit.buses" class="col-md-2">
          <h4>{{ $t("Buses") }}</h4>
          <p>{{ library.transit.buses }}</p>
        </div>
        <div v-if="library.transit.trams" class="col-md-2">
          <h4>{{ $t("Trams") }}</h4>
          <p>{{ library.transit.trams }}</p>
        </div>
        <div v-if="library.transit.trains" class="col-md-2">
          <h4>{{ $t("Trains") }}</h4>
          <p>{{ library.transit.trains }}</p>
        </div>
      </div>

      <div v-if="library.transit.parking">
        <h3>{{ $t("Parking instructions") }}</h3>
        {{ library.transit.parking }}
      </div>
    </section>

    <section v-if="hasContactInfo()" class="visual-section">
      <table class="table table-sm">
        <thead>
          <tr>
            <th>{{ $t('contact-info.department') }}</th>
            <th>{{ $t('contact-info.contact-details') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="department in departmentContactInfo">
            <th scope="row">{{ department.name || $t('contact-info.misc') }}</th>
            <td>
              <ul>
                <li v-for="entry in department.phones">
                  <a :href="`tel:+358${entry.number.replace(/\D/g, '').substr(1)}`">{{ entry.number}} </a> / {{ entry.name }}
                </li>
              </ul>
              <ul>
                <li v-for="entry in department.emails">
                  <a :href="`mailto:${entry.email}`">{{ entry.email }}</a> / {{ entry.name }}
                </li>
              </ul>
              <ul>
                <li v-for="entry in department.links">
                  <a :href="entry.url">{{ entry.url.replace(/^http(s?):\/\/(www\.?)/, '') }}</a> / {{ entry.name }}
                </li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </main>
</template>

<script>
  import { kirkanta, first, last } from '@/mixins'
  import { faQuoteRight, faEnvelope, faLink, faLongArrowAltLeft } from '@fortawesome/free-solid-svg-icons'

  import {
    faFacebookSquare,
    faFlickr,
    faInstagram,
    faPinterestSquare,
    faTwitterSquare,
    faVimeoSquare,
    faYoutube
  } from '@fortawesome/free-brands-svg-icons'

  const icon_map = new Map([
    [/facebook\.com/, faFacebookSquare],
    [/flickr\.com/, faFlickr],
    [/instagram\.com/, faInstagram],
    [/pinterest\.com/, faPinterestSquare],
    [/vimeo\.com/, faVimeoSquare],
    [/twitter\.com/, faTwitterSquare],
    [/youtube\.com/, faYoutube],
  ]);

  export default {
    data: () => ({
      library: null,
      refs: {},
      faQuoteRight,
      faEnvelope,
    }),
    computed: {
      sortedLinks() {
        if (this.library.links) {
          return this.library.links.sort((a, b) => {
            // NOTE: Horribly inefficient but whatever...

            let a_match = 0
            let b_match = 0

            for (let rx of icon_map.keys()) {
              if (a.url.match(rx)) {
                a_match = 1
              }

              if (b.url.match(rx)) {
                b_match = 1
              }
            }

            return a_match - b_match
          });
        } else {
          return null
        }
      },
      departmentContactInfo() {
        const departments = new Map([
          [null, {
            name: '',
            phones: [],
            emails: [],
            links: []
          }]
        ])

        for (let department of this.library.departments) {
          let { name, id, description } = department
          departments.set(id, {name, id, description, phones:[], emails: [], links: []})
        }

        for (let entry of this.library.phoneNumbers) {
          // if (entry.department) {
            departments.get(entry.department).phones.push(entry)
          // }
        }

        for (let entry of this.library.emailAddresses) {
          // if (entry.department) {
            departments.get(entry.department).emails.push(entry)
          // }
        }

        for (let entry of this.library.links) {
          // if (entry.department) {
            departments.get(entry.department).links.push(entry)
          // }
        }

        return [...departments.values()].sort((a, b) => {
          if (!a.name.length) {
            return 1000
          }
          if (!b.name.length) {
            return -1000
          }
          return a.name.localeCompare(b.name)
        })
      }
    },
    methods: {
      first, last,
      linkIcon(link) {
        let icon_class = faLink;

        for (let [rx, icon] of icon_map) {
          if (link.url.match(rx)) {
            icon_class = icon;
          }
        }

        return icon_class;
      },
      hasPublicTransportation() {
        if (this.library.transit) {
          for (let [field, info] of Object.entries(this.library.transit)) {
            if (info && info.length) {
              return true;
            }
          }
        }
        return false;
      },
      hasContactInfo() {
        return (this.library.links.length + this.library.emailAddresses.length + this.library.phoneNumbers.length) > 0;
      }
    },
    async created() {
      let response = await kirkanta.get('library', {
        'city.slug': this.$route.params.city,
        slug: this.$route.params.library,
        with: ['schedules', 'links', 'mailAddress', 'emailAddresses', 'phoneNumbers', 'departments'],
        refs: ['city', 'period'],
        'period.start': '1w',
        'period.end': '8w'
      })

      this.library = response.data
      this.refs = response.refs
    }
  }
</script>

<style lang="scss" scoped>
  @import "../scss/bootstrap/init";

  section {
    margin-bottom: spacing(3);
  }

  .fa-quote-right {
    margin-right: spacing(2);
  }

  .cover-photo-frame {
    display: flex;
    flex-flow: column;
    justify-content: center;
  }

  .cover-photo {
    width: 100%;
  }

  .info-link {
    display: inline-block;
    margin-right: spacing(3);
  }

  .visual-section {
    padding-left: spacing(3);
    margin-left: -1 * spacing(3);
    border-left: 5px solid black;
    margin-bottom: spacing(3);

    &:nth-child(1) {
      border-color: orange;
    }

    &:nth-child(2) {
      border-color: rgb(87, 129, 238);
    }

    &:nth-child(3) {
      border-color: rgb(195, 195, 195);
    }

    &:nth-child(4) {
      border-color: rgb(167, 244, 135);
    }

    &:nth-child(5) {
      border-color: rgb(219, 152, 255);
    }

    &:nth-child(6) {
      border-color: rgb(164, 102, 112);
    }
  }
</style>

<style lang="scss" scoped>
  .fa-facebook-square {
    color: #3b5998;
  }

  .fa-twitter-square {
    color: #1da1f2;
  }

  .fa-instagram {
    background-color: #904ac6;
    color: white;
    padding: 0 1px;
    box-sizing: content-box;
  }

  .fa-pinterest-square {
    color: #d63633;
  }

  .fa-youtube {
    color: red;
  }

  .fa-vimeo {
    color: #1ab7ea;
  }

  .fa-flickr {
    color: #e62683;
  }

  .fa-link {
    color: #444;
  }
</style>
