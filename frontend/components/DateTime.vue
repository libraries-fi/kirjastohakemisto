<!-- Simple element for displaying date and time values. -->
<template>
  <time v-if="wrap" :datetime="valueAttribute">{{ formattedDate }}</time>
  <span v-else>{{ formattedDate }}</span>
</template>

<script>
import { format, parseISO } from 'date-fns'

import fi from 'date-fns/esm/locale/fi'
import sv from 'date-fns/esm/locale/sv'
import en from 'date-fns/esm/locale/en-GB'

import { detectLanguage } from '@/mixins'
const langcode = detectLanguage()

const locales = new Map([
  ['fi', fi],
  ['en', en],
  ['sv', sv]
])

export default {
  props: ['date', 'time', 'format', 'formal', 'short'],
  computed: {
    locale () {
      if (langcode === 'fi') {
        return fi
      } else if (langcode === 'sv') {
        return sv
      } else {
        return en
      }
    },
    wrap: function () {
      return typeof this.formal !== 'undefined'
    },
    valueAttribute () {
      if (this.time) {
        return format(parseISO(`1970-02-01T${this.time}`), 'HH:mm')
      } else if (this.date) {
        return format(parseISO(this.date), 'yyyy-MM-dd')
      } else {
        return format(new Date(), 'yyyy-MM-dd')
      }
    },
    formattedDate () {
      if (this.format) {
        if (this.time) {
          return format(parseISO(`1970-02-01T${this.time}`), this.format, { locale: this.locale })
        } else if (this.date) {
          let short = typeof this.short !== 'undefined'
          let date = parseISO(this.date)
          let formatted = format(date, this.format, { locale: this.locale })

          if (short && this.format.substr(-1) === 'P' && date.getFullYear() === (new Date()).getFullYear()) {
            let formattedfi = format(date, this.format, { locale: fi })
            formatted = formattedfi.substr(0, formattedfi.length - 4).replace(/[\s,]+$/, '')
          }
          return formatted
        } else {
          return format(new Date(), this.format)
        }
      } else {
        return this.valueAttribute
      }
    }
  }
}
</script>
