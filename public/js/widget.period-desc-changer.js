/**
 * Watches weekly schedule for changes and show or hides periods descriptions
 * according to the current week.
 */
const PeriodDescChanger = (($) => {
  class PeriodDescChanger {
    constructor(element) {

      this._element = element;
      this._periods = $('.schedules-period .period-description', this._element);
      this._element.addEventListener('changed', (evt) => {
        this._weekChanged();
      });

      // Show current week descriptions.
      this._weekChanged();
    }

    _weekChanged() {
      let week = $('[data-current-page]', this._element).data('yearWeekNumber');
      this._periods.each(function (index, period) {
        let fromWeek = $(period).data('fromWeek');
        let toWeek = $(period).data('toWeek');

        // Both starting period and ending period must be defined.
        if(!fromWeek || !toWeek) {
          return;
        } 

        $(period).removeClass('active');

        if(week >= fromWeek && week <= toWeek) {
          $(period).addClass('active');
        }
      });
    } 

  }

  $.fn.kifiPeriodDescChanger = function() {
    return this.each((i, element) => {
      let widget = new PeriodDescChanger(element);
    });
  };
})(jQuery);

export default PeriodDescChanger;
