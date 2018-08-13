"use strict";

class Cookies {
  static parseCookies(cookie_str) {
    if (!cookie_str.length) {
      return [];
    }
    return cookie_str.split("; ").map(item => {
      if (item != "") {
        const pos = item.indexOf("=");
        return [item.substr(0, pos), item.substr(pos+1)];
      }
    });
  }

  constructor() {
    this.data = new Map(Cookies.parseCookies(document.cookie));
    console.log(this.data);
  }

  get(value) {
    return this.data.get(value);
  }

  set(key, value) {
    document.cookie = encodeURIComponent(key) + "=" + encodeURIComponent(value);
    this.data.set(value);
    return this;
  }
}

class Session {
  constructor() {
    this.data = new Cookies;
  }

  get(key, fallback) {
    return this.data.get(key);
  }

  set(key, value) {
    this.data.set(key, value);
    return this;
  }
}
