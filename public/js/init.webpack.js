import "../../translations/browser.fi.yaml";
import "../../translations/browser.sv.yaml";
import "../../translations/browser.en.yaml";

import "jquery/dist/jquery.min";
import "chosen-js/chosen.jquery.min";
import "bootstrap/dist/js/bootstrap.bundle.min";

import "./init.i18next";
import "./init.twig";
import "./init.deobfuscate-email";

import "./utils/custom-events";
import "./utils/geolocation";
import "./widget.details";
import "./widget.gallery";
import "./widget.switch";
import "./widget.paged-table";
import "./widget.load-more";
import "./widget.content-peek";
import "./widget.period-desc-changer";
import "./widgets";

import "./init.ie-warning";

import "./library.search";

if (!global._babelPolyfill) {
  require("@babel/polyfill");
}

/*
 * NOTE: Initialize base widgets first, then the larger components like form controllers etc.
 * This is because the controllers will depend upon the base widgets.
 */
$("[data-widget-switch]").kifiSwitch();
$("[data-paged-table]").kifiPagedTable();
$("[data-load-more]").kifiLoadMore();
$("[data-gallery]").kifiGallery();
$("[data-content-peek]").kifiContentPeek();
$(".details").kifiDetails();
$(".schedules-weekly").kifiPeriodDescChanger();

const popovers = $("[data-toggle=\"popover\"]").popover({
  placement: "bottom",
  template: $('[type="text/x-service-popover"]').text(),
  html: true,
  // trigger: "manual",
  content: function() {
    return document.querySelector(this.dataset.target).innerHTML;
  }
});

popovers
  .on("click", function(event) {
    event.preventDefault();
  })
  .on("show.bs.popover", function(event) {
    popovers.not(this).popover("hide");
    this.setAttribute("data-scroll-target", "");
  })
  .on("shown.bs.popover", function(event) {
    let button = $("body").find(".service-popover").find("button");

    button.focus().on("click", (event) => {
      $(this).focus().popover("hide");
    });
  })
  .on("hide.bs.popover", function(event) {
    this.removeAttribute("data-scroll-target");
  });

try {
  if (window.location.hash) {
    popovers.filter(window.location.hash).trigger("click");
  }
} catch (e) {

}

/*
 * Translations are loaded in async mode despite being bundled in, so give them some time.
 *
 * At this time this is the only place that'll trigger in-browser translation, so this hack
 * will suffice for now.
 */
setTimeout(() => {
  let form = $("#the-search-form");

  form.librarySearch().trigger("submit", {shouldKeepOldResults: true});

  let input = form.serializeArray().filter((item) => {
    return item.value != "" &&  ["geo", "q"].indexOf(item.name) == -1;
  });

  if (input.length > 0) {
    $("#toggle-advanced-search").trigger("click");
  }
}, 10);

import { MapView } from "./widget.map-view";
import FullScreen from "ol/control/fullscreen";

// MapView.openMap({pos: [24.93144000,60.17249000], userLocation: [24.933907, 60.20090972] });

$('[data-map]').each((i, element) => {
  let [lat, lon] = element.dataset.map.split(/[,\s]+/);

  let options = {
    pos: [lon, lat],
    controls: [new FullScreen],
  };

  if (element.dataset.scrollBlock) {
    options.controls.push(new FullScreen({
      target: element.dataset.scrollBlock
    }));
  }

  let map = new MapView(element, options);
  map.show();
});
