const LoadMore = (($) => {
  const LOCK_INTERVAL = 1000;

  class LoadMore {
    constructor(button) {
      this._button = button;
      this._allowed = false;
      this._form = $(button).closest("form")[0];

      $(window).on("scroll", (event) => {
        if (this.enabled) {
          let scroll = window.scrollY + window.innerHeight;
          let limit = $(this._button).offset().top;

          if (limit > 0 && (scroll - limit > 100)) {
            $(this._button).trigger("click");
          }
        }
      });

      $(this._button).on("click", (event) => {
        this._allowed = true;
      });
    }

    get enabled() {
      return this._allowed && !this._form.hasAttribute("data-loading");
    }
  }

  $.fn.kifiLoadMore = function() {
    return this.each((i, element) => {
      let widget = new LoadMore(element);
    });
  };

  return LoadMore;
})(jQuery);

export { LoadMore };
