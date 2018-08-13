const ContentPeek = (($) => {

  class ContentPeek {
    constructor(element, config) {
      this._element = element;
      this._config = config;

      $(this._element).find("[data-peek-toggle]").on("click", (event) => {
        event.preventDefault();
        this.toggle();
      });
    }

    get expanded() {
      return this._element.hasAttribute("data-expanded");
    }

    toggle() {
      if (this.expanded) {
        this.collapse();
      } else {
        this.expand();
      }
    }

    expand() {
      this._element.setAttribute("data-expanded", "");
      $(this._element).find("[data-peek-toggle]").attr("aria-expanded", "true")
    }

    collapse() {
      this._element.removeAttribute("data-expanded");
      $(this._element).find("[data-peek-toggle]").attr("aria-expanded", "false")
    }
  }

  $.fn.kifiContentPeek = function() {
    this.each((i, elem) => {
      let peek = new ContentPeek(elem);

      // $(elem).find([])
    });
  };
})(jQuery);

export default ContentPeek;
