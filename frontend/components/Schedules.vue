<template>
  <div class="weekly-schedules" :data-expand-mode="expandMode" v-if="schedules">
    <div class="toolbar">
      <button type="button" @click="previousWeek" class="btn btn-link btn-sm">
        <fa :icon="faWeekPrev"/>
      </button>
      <h3 class="week-label">{{ $t('schedules.week') }} {{ week }}</h3>
      <button type="button" @click="nextWeek" class="btn btn-link btn-sm">
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

      <tbody v-for="(day, index) of schedules.slice(this.i * 7, (this.i + 1) * 7)" :class="isToday(day) ? 'current-day' : null" :data-expanded="index == expandedRow">
        <tr class="day-entry">
          <th :rowspan="(day.times ? day.times.length : 0 ) + 1 + (day.info ? 1 : 0)" scope="row" class="col-date">
            <date-time :date="day.date" format="P" formal short/>
          </th>
          <td class="col-weekday">
            <date-time :date="day.date" format="cccc"/>

            <button class="btn btn-link" @click="expandedRow = index" v-if="expandMode == 'current' && !day.closed">
              <fa :icon="faCollapse" v-if="index == expandedRow"/>
              <fa :icon="faExpand" v-else/>
            </button>
          </td>
          <td v-if="day.closed" class="col-time closed">{{ $t('schedules.closed') }}</td>
          <td v-else class="col-time">
            <date-time :time="day | opens" format="p" formal/>
            <date-time :time="day | closes" format="p" formal/>
          </td>
        </tr>
        <tr v-for="time of day.times" class="time-entry" :class="['closed', 'regular', 'self-service'][time.status]">
          <td v-if="time.status == 0" class="col-status">{{ $t("schedules.closed") }}</td>
          <td v-if="time.status == 1" class="col-status">{{ $t("schedules.staffed") }}</td>
          <td v-if="time.status == 2" class="col-status">{{ $t("schedules.self-service") }}</td>

          <td class="col-time">
            <date-time :time="time.from" format="p"/>
            <date-time :time="time.to" format="p"/>
          </td>
        </tr>
        <tr v-if="day.info" class="day-info text-muted">
          <td colspan="2">{{ day.info }}</td>
        </tr>
      </tbody>
    </table>

    <div class="period-info" v-if="periodInfo.length">
      <template v-for="period of periodInfo">
        <p v-if="period.description">
          <b v-if="period.valid_until">
            <date-time :date="period.valid_from" format="P" formal short/> –
            <date-time :date="period.valid_until" format="P" formal short/>:
          </b>
          <b v-else>
            From <date-time :date="period.valid_from" format="P" formal short/>:
          </b>
          <span>{{ period.description }}</span>
        </p>
      </template>
    </div>
  </div>
</template>

<script>
import { format, isSameDay, toDate } from 'date-fns'
import { first, last } from '@/mixins'

import { faAngleDoubleLeft, faAngleDoubleRight, faMinusSquare } from '@fortawesome/free-solid-svg-icons'
import { faPlusSquare } from '@fortawesome/free-regular-svg-icons'

import DateTime from './DateTime.vue'

export default {
  components: { DateTime },
  props: {
    schedules: {},
    periods: {},
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
        return day ? format(toDate(day.date), 'I') : null
      }
    },
    periodInfo () {
      let filtered = []
      for (let pid in this.periods) {
        if (this.periods[pid].description) {
          filtered.push(this.periods[pid])
        }
      }
      return filtered
    },
    faWeekPrev: () => faAngleDoubleLeft,
    faWeekNext: () => faAngleDoubleRight,
    faExpand: () => faPlusSquare,
    faCollapse: () => faMinusSquare
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
      return isSameDay(day.date, new Date())
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
          time.status = time.staff ? 1 : 2
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
    color: $text-muted;
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
    background-color: theme-color("light");
    // border-top: 1px solid $border-light !important;
    // border-bottom: 1px solid $border-light !important;
  }

  .day-info {
    font-size: smaller;
  }

  .period-info {
    border-top: 3px dashed $table-border-color;
    padding: spacing(1);

    span {
      white-space: pre-line;
    }
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

    .time-entry {
      display: none;
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
