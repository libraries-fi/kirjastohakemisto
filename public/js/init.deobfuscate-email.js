/**
 * Deobfuscate email addresses protected against spam.
 * Email addresses must be printed on the page in ROT13-encoded format.
 * Character '@' can be replaced with '#'.
 */

$(() => {
  /*
   * Source: https://gist.github.com/dsoares/c65f1bacac0acd46fd9f
   *
   * NOTE: Only handles lower-cased strings.
   */
  function rot13(str) {
    var re = new RegExp("[a-z]", "i");
    var min = 'A'.charCodeAt(0);
    var max = 'Z'.charCodeAt(0);
    var factor = 13;
    var result = "";
    str = str.toUpperCase();

    for (var i=0; i<str.length; i++) {
      result += (re.test(str[i]) ?
        String.fromCharCode((str.charCodeAt(i) - min + factor) % (max-min+1) + min) : str[i]);
    }

    return result.toLowerCase();
  }

  $('.secure-email').each((i, elem) => {
    const email = rot13(elem.textContent).replace('#', '@');

    if ("noLink" in elem.dataset) {
      elem.textContent = email;
      elem.className = elem.className.replace(/\bsecure-email\b/, '');
    } else {
      let link = document.createElement("a");
      link.href = `mailto:${email}`;
      link.textContent = email;

      elem.parentElement.insertBefore(link, elem);
      elem.remove(elem);
    }
  });
});
