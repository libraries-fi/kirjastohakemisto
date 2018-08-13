const CustomEvents = (($) => {
  class CustomEvents {
    static trigger(element, event_id) {
      try {
        let event = new CustomEvent(event_id);
        element.dispatchEvent(event);
      } catch (e) {
        let event = document.createEvent("CustomEvent");
        event.initCustomEvent(event_id, false, false, {detail: null});
        element.dispatchEvent(event);
      }
    }

    static toButton(element) {
      element.addEventListener("keypress", (event) => {

        switch (event.key) {
          case "Enter":
          case " ":
            event.preventDefault();
            CustomEvents.trigger(element, "click");
            break;
        }
      });
    }
  }

  return CustomEvents;
})(jQuery);

export { CustomEvents };
