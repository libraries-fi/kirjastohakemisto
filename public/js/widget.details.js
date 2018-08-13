const Details = (($) => {
  const CLASS_EXPANDED = "expanded";

  class Details {
    constructor(element, config) {
      this._element = element;
      this._config = config;
    }

    get button() {
      if (!this._button) {
        this._button = this._element.querySelector("[data-toggle=\"details\"]")
      }
      return this._button;
    }

    toggle() {
      if ($(this._element).hasClass(CLASS_EXPANDED)) {
        this.close();
      } else {
        this.open();
      }
    }

    open() {
      $(this._element).addClass(CLASS_EXPANDED);
      this.button.setAttribute("aria-expanded", "true");
    }

    close() {
      $(this._element).removeClass(CLASS_EXPANDED);
      this.button.setAttribute("aria-expanded", "false");
    }
  }

  $.fn.kifiDetails = function() {
    this.each(function() {
      let details = new Details(this);
      let button = $(this).find('[data-toggle="details"]').on("click", (event) => {
        event.preventDefault();
        details.toggle();
      });

      if (button.attr("aria-controls") == window.location.hash.substr(1)) {
        details.open();
      }
    });
  };

  return Details;
})(jQuery);

export default Details;
