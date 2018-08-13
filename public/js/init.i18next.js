import i18next from "i18next/dist/es/i18next";
import sprintf from "i18next-sprintf-postprocessor";

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

i18next.addResourceBundle("fi", "app", require("../dist/browser.fi.json"));
i18next.addResourceBundle("en", "app", require("../dist/browser.en.json"));
i18next.addResourceBundle("sv", "app", require("../dist/browser.sv.json"));
