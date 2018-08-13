
const PagedTable = (($) => {
  class PagedTable {
    constructor(element, config) {
      this._element = element;
      this._config = config;

      this._buttonBack = this._element.querySelector("[data-previous-page]");
      this._buttonNext = this._element.querySelector("[data-next-page]");

      this._buttonBack.addEventListener("click", () => this.previousPage());
      this._buttonNext.addEventListener("click", () => this.nextPage());

      // Make sure that at least one page is selected.
      this.currentPage;
    }

    _setPageState(page, state) {
      if (state) {
        page.setAttribute("data-current-page", "true");
      } else {
        page.removeAttribute("data-current-page");
      }
    }

    get currentPage() {
      let current = this._element.querySelector("[data-current-page]");

      if (!current) {
        current = this._element.querySelector("tbody");
        this._setPageState(current, "true");
      }

      return current;
    }

    selectPage(index) {
      let current = this.currentPage;
      let pages = current.parentElement.children;

      if (index in pages) {
        this._setPageState(pages[index], true);
        this._setPageState(current, false);
      }
    }

    previousPage() {
      let current = this.currentPage;

      if (current.previousElementSibling) {
        this._setPageState(current, false);
        this._setPageState(current.previousElementSibling, true);
      }
    }

    nextPage() {
      let current = this.currentPage;

      if (current.nextElementSibling) {
        this._setPageState(current, false);
        this._setPageState(current.nextElementSibling, true);
      }
    }
  }

  $.fn.kifiPagedTable = function() {
    return this.each((i, element) => {
      let widget = new PagedTable(element);
    });
  };
})(jQuery);

export default PagedTable;
