if (navigator.userAgent.match(/Trident/)) {
  let langcode = document.documentElement.lang;
  let translations = {
    fi: "Käytät vanhentunutta selainta – päivitä selaimesi",
    en: "Your web browser is too old -- update your browser",
    sv: "Gammal webbläsare – uppdatera",
  };
  const message = translations[langcode];
  const banner = $("<div/>").addClass("alert alert-danger")
    .append('<i class="fa fa-exclamation-circle mr-3"/>')
    .append($("<span/>").text(message))
    ;

  const options = $("<ul/>")
    .append($("<li/>").html('<a href="https://www.mozilla.org">Mozilla Firefox</a>'))
    .append($("<li/>").html('<a href="https://www.google.com/chrome">Google Chrome</a>'))
    .append($("<li/>").html('<a href="https://www.opera.com">Opera</a>'))
    ;

  banner.append(options);

  $("main").prepend(banner);
}
