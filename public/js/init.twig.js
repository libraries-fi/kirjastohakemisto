import { format, parse, isAfter, isToday, isTomorrow } from "date-fns";
import i18next from "i18next/dist/es/i18next";
import Twig from "twig";

(function($, Twig) {
  function twig_path(route_name, parameters) {
    const routes = new Map([
      ["library.show", "/{city}/{slug}"]
    ]);

    if (routes.has(route_name)) {
      let proto = routes.get(route_name);
      for (let key of Object.keys(parameters)) {
        proto = proto.replace("{" + key + "}", parameters[key]);
      }
      return proto;
    } else {
      return "#noroute";
    }
  }

  function twig_library_service_time(schedules) {
    if (!schedules.length) {
      return null;
    }

    let now = new Date;
    let hour_min = "HH:mm";

    for (let day of schedules) {
      let opens = day.opens ? parse(day.date + " " + day.opens) : null;
      let closes = day.closes ? parse(day.date + " " + day.closes) : null;

      if (isToday(day.date)) {
        if (isAfter(opens, now)) {
          // let label = twig_trans("Opens at %s");
          let time = format(opens, hour_min);
          return twig_trans("status.opens_at_time", [time])
        } else if (isAfter(closes, now)) {
          return [format(opens, hour_min), format(closes, hour_min)].join(" – ");
        }
      } else if (isTomorrow(day.date) && opens) {
        let time = format(opens, hour_min) + " – " + format(closes, hour_min);
        let label = twig_trans("tomorrow");
        let html = ' <span style="white-space: nowrap">' + time + '</span>';
        return label + html;
      }
    }

    return '<em>' + twig_trans('check_schedules') + '</em>';
  }

  function twig_trans(text, ...args) {
    if (args.length > 0) {
      return i18next.t(text, {
        postProcess: "sprintf",
        sprintf: args
      });
    } else {
      return i18next.t(text);
    }
  }

  Twig.extendFilter('trans', twig_trans);
  Twig.extendFilter('statusDescription', twig_library_service_time);
  Twig.extendFunction('path', twig_path);

  $('[type="text/x-twig"]').each((i, element) => {
    Twig.twig({
      id: element.id,
      data: atob(element.innerText),
    });
  });
}(jQuery, Twig));
