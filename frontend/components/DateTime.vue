<!-- Simple element for displaying date and time values. -->
<template>
  <time v-if="wrap" :datetime="valueAttribute">{{ formattedDate }}</time>
  <span v-else>{{ formattedDate }}</span>
</template>

<script>
  import format from "date-fns/format";
  import toDate from "date-fns/toDate";

  import fi from "date-fns/esm/locale/fi";
  import sv from "date-fns/esm/locale/sv";
  import en from "date-fns/esm/locale/en-US";

  const locales = { fi, sv, en };

  export default {
    props: ["date", "time", "format", "formal", "short"],
    computed: {
      locale() {
        let p = this.$parent;
        while (p.$parent) { p = p.$parent; }

        if (p.options && p.options.lang) {
          return locales[p.options.lang];
        } else {
          return fi;
        }
      },
      wrap: function() {
        return typeof this.formal != "undefined";
      },
      valueAttribute: function() {
        if (this.time) {
          return format(toDate("1970-02-01 " + this.time), "HH:mm");
        } else if (this.date) {
          return format(toDate(this.date), "YYYY-MM-dd");
        } else {
          return format(new Date, "YYYY-MM-dd");
        }
      },
      formattedDate: function() {
        if (this.format) {
          if (this.time) {
            return format(toDate("1970-02-01 " + this.time), this.format, {locale: this.locale});
          } else if (this.date) {
            let short = typeof this.short != "undefined";
            let date = toDate(this.date);
            let formatted = format(date, this.format, {locale: this.locale});

            if (short && this.format.substr(-1) == "P" && date.getFullYear() == (new Date).getFullYear()) {
              formatted = formatted.substr(0, formatted.length - 4).replace(/[\s,]+$/, "");
            }
            return formatted;
          } else {
            return format(new Date, this.format);
          }
        } else {
          return this.valueAttribute;
        }
      }
    }
  };
</script>
