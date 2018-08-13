import { locator } from "./utils/geolocation";
import { CustomEvents } from "./utils/custom-events";
import { MapView } from "./widget.map-view";
import Twig from "twig";
import i18next from "i18next/dist/es";

const LibrarySearch = (($, Twig) => {
  const PAGE_SIZE = 10;
  const MAX_GEO_DISTANCE = "20km";

  class Url {
    constructor(path, query, options) {
      this.path = path || "";
      this.query = new Map(query || []);
      this.options = options || {};
    }

    get basePath() {
      return this.options.basePath || "";
    }

    appendPath(path) {
      this.path = this.path.split("/").filter().push(path).join("/");
    }

    setQuery(key, value) {
      if (key.substr(-2) == "[]") {
        key = key.substr(0, key.length - 2);

        if (!this.query.get(key)) {
          this.query.set(key, new Set(value));
        } else {
          this.query.get(key).add(value);
        }
      } else {
        this.query.set(key, value);
      }
    }

    toString() {
      let query = [...this.query].filter(d => d[1] != "");
      let path = this.basePath + this.path;

      if (query.length > 0) {
        path += "?" + query.map(item => {
          if (item[1] instanceof Set) {
            return [...item[1]].map(v => [item[0] + "[]", encodeURIComponent(v)].join("=")).join("&");
          } else {
            return item.join("=");
          }
        }).join("&")
      }

      return path;
    }
  }

  class LibrarySearch {
    constructor(form, config) {
      this._form = form;
      this._config = config || {};

      this.lastLocation = null;

      form = $(form);

      form.on("submit", (event, ...extra) => this.submit(event, ...extra));

      form.find("[data-next-page]").on("click", (event) => this.onRequestMoreResults(event));
      form.find(":input").on("change", (event) => form.submit());

      form.find('input[type="search"]').on("keypress", (event) => {
        if (event.key == "Enter") {
          // Blur search field to allow virtual KBDs close etc.
          form.find('button[type="submit"]').focus();
        }
      });

      this.currentPage = parseInt(window.location.pathname.split("/").pop()) || 1;

      let path = this.currentPage > 1 ? "/" + this.currentPage : "";

      this.url = new Url(path, form.serializeArray().map(d => [d.name, d.value]), {
        basePath: "/search"
      });

      this.saveHistory(true);
    }

    isBusy() {
      return this._form.hasAttribute("data-loading");
    }

    serializeData() {
      return
    }

    isLocationEnabled() {
      return this._form.querySelector('input[name="geo"]').checked;
    }

    saveHistory(replace) {
      this.url.path = this.currentPage > 1 ? "/" + this.currentPage : "";

      let state = history.state || {};

      Object.assign(state, {
        page: this.currentPage,
        location: this.isLocationEnabled(),
      });

      if (replace) {
        history.replaceState(state, "", this.url.toString());
      } else {
        history.pushState(state, "", this.url.toString());
      }
    }

    onRequestMoreResults(event) {
      this.currentPage += 1;
      this.saveHistory();

      this.submit(event, {shouldKeepOldResults: true});
    }

    submit(event, extra) {
      event.preventDefault();

      if (this.isBusy()) {
        return;
      }

      this._form.setAttribute("data-loading", "");

      let keep_old_result = extra ? extra.shouldKeepOldResults : false;

      let inputs = $(this._form).find(":input").filter((i, input) => {
        return $(input).val() != "";
      });

      let data = $(inputs).serializeArray();
      let path = keep_old_result ? location.pathname : "/search";

      if (!keep_old_result) {
        this.currentPage = 1;
      }

      this.url.query.clear();
      data.forEach(d => this.url.setQuery(d.name, d.value));

      this.saveHistory(true);

      Promise.resolve(this.isLocationEnabled() ? locator.locate() : null)
        .then(pos => pos)
        .catch(error => console.error(error))
        .then(pos => {
          if (pos && pos.coords) {
            data.push({
              name: "pos",
              value: [pos.coords.latitude, pos.coords.longitude].join(","),
            });
          }
          $.post(path, data)
            .done((result) => {
              this._form.removeAttribute("data-loading");
              // console.log("OK", result);
              this.showResult(result, !keep_old_result);
            })
            .fail((error) => {
              alert("An error occurred:", error);
            });
        });
    }

    mergeRefs(library, refs) {
      if (refs) {
        for (let key of Object.keys(refs)) {
          library[key] = refs[key][library[key]];
        }
      }
      return library;
    }

    showResult(result, replace) {
      let items = result.items.map((library) => {
        let html = Twig.twig({ref: "twig-library-card"}).render({library: library});
        return $(html)[0];
      });

      // $(items).find("[data-map-open]").on("click", (event) => {
      //   console.log("OPEN MAP");
      // });

      $(items).find("[data-map-open]").on("click", (event) => this.openMap(event));
      let container = $(this._form).find("[data-search-results]");

      let info = $(this._form).find("#search-total");

      if (!info.length) {
        info = $("<p/>")
          .attr("id", "search-total")
          .attr("tabindex", "-1")
          .insertBefore(container);
      }

      info.text(i18next.t("search.total", {
        postProcess: "sprintf",
        sprintf: [result.total]
      }));

      if (replace == true || replace == undefined) {
        container.html(items);
      } else {
        container.append(items);
      }

      let pager = $(this._form).find("[data-load-more]");
      let pos = history.state.page * PAGE_SIZE;

      if (pos < result.total) {
        this._form.removeAttribute("data-pager-finished");
      } else {
        this._form.setAttribute("data-pager-finished", "");
      }
    }

    openMap(event) {
      let coords = event.target.dataset.mapPos.split(",");
      let user_pos = null;

      if (locator.lastLocation) {
        user_pos = [locator.lastLocation.coords.longitude, locator.lastLocation.coords.latitude];
      }

      MapView.openMap({
        pos: coords,
        userLocation: user_pos,
        fullscreen: true,
      });
    }
  }

  $.fn.librarySearch = function() {
    return this.each((i, form) => {
      let search = new LibrarySearch(form);
      let gps_toggle = form.querySelector("[data-toggle-location]");

      // $(gps_toggle).on("changed", (event) => {
      //   console.log("CHANGED", event.target.checked);
      // });

      if (gps_toggle.checked) {
        CustomEvents.trigger(gps_toggle, "change");
      }
    });
  };
})(jQuery, Twig);

export default LibrarySearch;
