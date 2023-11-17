<template>
  <div class="weekly-schedules" :data-expand-mode="expandMode" v-if="schedules">
    <div class="toolbar border">
      <button type="button" @click="previousWeek" class="btn btn-link btn-sm px-3" :aria-label="$t('schedules.previous-week')">
        <fa :icon="faWeekPrev"/>
      </button>
      <h3 class="week-label bg-light">{{ $t('schedules.week') }} {{ week }}</h3>
      <button type="button" @click="nextWeek" class="btn btn-link btn-sm px-3" :aria-label="$t('schedules.next-week')">
        <fa :icon="faWeekNext"/>
      </button>
    </div>

    <table class="table table-sm table-borderless mb-0 schedules">
      <thead class="sr-only">
        <tr>
          <th class="col-date">{{ $t('schedules.date') }}</th>
          <th class="col-weekday">{{ $t('schedules.day') }}</th>
          <th class="col-time">{{ $t('schedules.times') }}</th>
        </tr>
      </thead>
      <tbody v-for="(day, index) of schedules.slice(this.i * 7, (this.i + 1) * 7)" :class="isToday(day) ? 'current-day' : null">
        <tr class="day-entry font-weight-bolder">
          <th :rowspan="(day.times ? day.times.length : 0 ) + 1 + (day.info ? 1 : 0)" scope="row" class="col-date pt-2">
            <date-time :date="day.date" format="P" formal short/>
          </th>
          <td class="col-weekday pt-2">
            <date-time :date="day.date" format="cccc"/>
          </td>
          <td v-if="day.closed" class="col-time closed pt-2">{{ $t('schedules.closed') }}</td>
          <td v-else class="col-time pt-2">
            <date-time :time="day | opens" format="p" formal/>
            <date-time :time="day | closes" format="p" formal/>
          </td>
        </tr>
        <tr v-if="day.info" class="day-info">
          <td colspan="2" class="pt-0">{{ day.info }}</td>
        </tr>
        <tr v-for="time of day.times" class="time-entry" :class="['closed', 'regular', 'self-service'][time.status]">
          <td v-if="time.status == 0" class="col-status pt-0">{{ $t("schedules.closed") }}</td>
          <td v-if="time.status == 1" class="col-status pt-0">{{ $t("schedules.staffed") }}</td>
          <td v-if="time.status == 2" class="col-status pt-0">{{ $t("schedules.self-service") }}</td>
          <td class="col-time pt-0">
            <date-time :time="time.from" format="p"/>
            <date-time :time="time.to" format="p"/>
          </td>
        </tr>
      </tbody>
    </table>

  </div>
</template>

<script>
import { format, isSameDay, parseISO } from 'date-fns'
import { first, last } from '@/mixins'

import { faAngleLeft, faAngleRight } from '@fortawesome/free-solid-svg-icons'

import DateTime from './DateTime.vue'

export default {
  components: { DateTime },
  props: {
    schedules: {},
    expandMode: {
      default: 'current'
    }
  },
  data: () => ({
    i: 0,
    expandedRow: parseInt(format(new Date(), 'i')) - 1
  }),
  computed: {
    week () {
      if (this.schedules.length > 0) {
        let day = this.schedules[this.i * 7]
        return day ? format(parseISO(day.date), 'I') : null
      }
    },
    faWeekPrev: () => faAngleLeft,
    faWeekNext: () => faAngleRight
  },
  methods: {
    first,
    last,
    toggleDay (event) {
      event.expanded = true
    },
    previousWeek () {
      this.i = Math.max(this.i - 1, 0)
    },
    nextWeek () {
      this.i = Math.min(this.i + 1, (this.schedules.length / 7) - 1)
    },
    isToday (day) {
      return isSameDay(parseISO(day.date), new Date())
    }
  },
  filters: {
    opens (day) {
      return first(day.times).from
    },
    closes (day) {
      return last(day.times).to
    }
  },
  created () {
    this.schedules.forEach((day) => {
      if (day.times) {
        day.times.forEach((time) => {
          time.status == time.staff ? 1 : 2
        })
      }
    })
  }
}
</script>

<style lang="scss">
  @import "../scss/bootstrap/init";

  th[scope="row"] {
    font-weight: unset;
  }

  .toolbar {
    display: flex;

    button {
      color: unset;
    }

    h2 {
      line-height: 1.8;
    }
  }

  .schedules tbody {
    border-top: 1px solid $border-color !important;
  }

  .schedules tbody:first-of-type {
    border-top: none !important;
  }

  .week-label {
    text-align: center;
    flex-grow: 1;
    padding: $nav-link-padding-y $nav-link-padding-x;
    margin: 0;
  }

  .col-date {
    width: 1rem;
    white-space: nowrap;
    text-align: center;
  }

  .col-weekday {
    width: 300px;
    text-align: left;
  }

  .col-time {
    width: 1rem;
    white-space: nowrap;
    text-align: center;

    :last-child:before {
      content: " – ";
    }
  }

  .day-entry {
    .closed {
      text-transform: lowercase;
    }
  }

  .time-entry {
    font-size: smaller;

    &.closed {
      color: theme-color("danger");
    }
  }

  .day-entry + .time-entry {
    th, td {
      padding-top: 0;
    }
  }

  .current-day {
    background-color: $green-light;
  }

  .day-info {
    font-size: smaller;
  }

  .weekly-schedules[data-expand-mode="current"] {
    .col-weekday button {
      padding: 0;
      line-height: inherit;
      float: right;
    }

    .day-entry {
      th, td {
        padding-top: spacing(1);
        padding-bottom: spacing(1);
      }
    }

    tbody[data-expanded] {
      .time-entry {
        display: table-row;
      }
    }
  }

  .weekly-schedules[data-expand-mode="none"] {
    .time-entry {
      display: none !important;
    }
  }

  .weekly-schedules[data-expand-mode="all"] {

  }

  .weekly-schedules[data-no-browse] {
    .toolbar,
    .col-date {
      display: none;
    }
  }
</style>
