import i18next from "i18next/dist/es/i18next";
import sprintf from "i18next-sprintf-postprocessor";

import fi from "browser.fi.json";
import sv from "browser.sv.json";
import en from "browser.sv.json";

const options = {
  lng: document.documentElement.lang,
  defaultNS: "app",
  // fallbackNS: "translation",
};

i18next.use(sprintf).init(options, (err, t) => {
  if (err) {
    console.error("Failed to initialize i18next:", err);
  }
});

i18next.addResourceBundle("fi", "app", fi);
i18next.addResourceBundle("en", "app", en);
i18next.addResourceBundle("sv", "app", sv);
