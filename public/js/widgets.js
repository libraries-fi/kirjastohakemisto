"use strict";

import i18next from "i18next/dist/es/i18next";

// i18next uses async initialization so we have to defer Chosen init too.
$(() => {
  $("select[multiple]").chosen({
    width: "100%",
    multiple: true,
    placeholder_text_single: i18next.t("choose"),
    placeholder_text_multiple: i18next.t("choose"),
  });

  $("select").chosen({
    width: "100%",
    allow_single_deselect: true,
    placeholder_text_single: i18next.t("choose"),
  });
});

$('[data-app="multi-select"]').each((i, widget) => {
  // Replaced with Chosen.
  return;

  let source = $(widget.dataset.source);
  let options = source.find("option");
  let input = $(widget).find('input[type="search"]');
  let values = $(widget).find(".multi-select-values");
  let popup = $("<div class=\"multi-select-popup\"><ul><li>First</li><li>Second</li></ul></div>")
    .attr("aria-role", "dialog")
    .insertAfter(input);

  popup.hide();

  source.on("change", (event) => {
    let tags = [];

    source.find("option:selected").each((i, option) => {
      let close = $("<button></button>")
        .attr("type", "button")
        .attr("class", "close")
        .attr("aria-label", "Remove")
        .html("&times;")

      let tag = $("<li></li>")
        .attr("data-value", option.value)
        .append($("<span></span>").text(option.innerText))
        .append(close);

      tags.push(tag);

      close.on("click", () => {
        source.find('option[value="' + tag.attr("data-value") + '"]').prop("selected", false);
        tag.remove();
      });
    });

    values.empty().append(tags);
  });

  input.on("focusout", () => {
    if (!popup.forceOpen) {
      popup.empty().hide();
    }
  });

  input.on("focusin", () => {
    popup.show();
    input.trigger("keyup");
  });

  input.on("keydown", (event) => {
    popup.forceOpen = true;
  });

  input.on("keyup", (event) => {
    popup.forceOpen = false;

    let text = event.target.value;
    let regex = new RegExp(text, "i");
    let matches = options.filter((i, option) => regex.test(option.innerText));
    let list_items = [];

    matches.each((i, option) => {
      let row = $("<li></li>")
        .attr("tabindex", i + 1)
        .attr("data-value", option.value).text(option.innerText);

      row.on("click", () => {
        option.selected = true;
        source.trigger("change");
        popup.hide();
      });
      row.on("mousedown", () => {
        popup.forceOpen = true;
      });
      row.on("mouseup", () => {
        popup.forceOpen = false;
      });
      list_items.push(row);
    });

    popup.empty().append($("<ul></ul>").append(list_items)).show();
  });


  /*

    let row = $("<li></li>")
      .append($("<span></span>").text(item.innerText))
      .append($("<button></button>")
        .attr("type", "button")
        .attr("class", "close")
        .attr("aria-label", "Remove")
        .html("&times;"));

  */
});
