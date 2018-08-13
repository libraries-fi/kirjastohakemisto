import { CustomEvents } from "./utils/custom-events";

const Switch = (($) => {
  class Switch {
    constructor(element, config) {
      this._element = element;
      this._config = config;

      if (element.dataset.input) {
        this._input = document.querySelector(element.dataset.input);
      } else {
        this._input = element.querySelector('input[type="checkbox"]');
      }

      if (!this._input) {
        this._input = document.createElement("input");
        this._input.type = "checkbox";
      }
      this.init();
      this.observe();
    }

    init() {
      CustomEvents.toButton(this._element);
      this._element.setAttribute("tabindex", "0");
      this._element.setAttribute("role", "button");
      this._element.setAttribute("aria-pressed", this.checked ? "true" : "false");
      this._element.addEventListener("click", (event) => this.onClick(event));
      this._input.addEventListener("change", (event) => this.onInputChange(event), true);

      Object.defineProperty(this._element, "checked", {
        get: () => this.checked,
        set: (value) => { this.checked = value; }
      });

      Object.defineProperty(this._element, "disabled", {
        get: () => this.disabled,
        set: (value) => { this.disabled = value; }
      });
    }

    observe() {
      let observer = new MutationObserver((records) => {
        switch (records[0].attributeName) {
          case "aria-pressed":
            this.checked = this._element.getAttribute("aria-pressed") == "true";
            break;
        }
      });

      observer.observe(this._element, {
        attributes: true,
        attributeFilter: ["aria-pressed"],
      });
    }

    get checked() {
      return this._input.checked;
    }

    set checked(state) {
      if (this._input.checked != state) {
        this._input.checked = state;

        this._element.checked = this.checked;
        this._element.setAttribute("aria-pressed", this.checked ? "true" : "false");

        CustomEvents.trigger(this._input, "change");
      }
    }

    get disabled() {
      return this._input.disabled;
    }

    set disabled(state) {
      this._input.disabled = state;
    }

    onClick(event) {
      event.preventDefault();
      this.checked = !this.checked;
      CustomEvents.trigger(this._element, "change");
    }

    onInputChange(event) {
      // event.preventDefault();
    }
  }

  $.fn.kifiSwitch = function() {
    return this.each((i, element) => {
      let button = new Switch(element);
    });
  };
})(jQuery);

export default Switch;
