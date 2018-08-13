import Twig from "twig";

const Gallery = (($) => {
  class Gallery {
    static launch() {
      return new Promise((resolve, reject) => {
        $.post("").done((result) => {
          let gallery = new Gallery(result.pictures);

          gallery.show();

          resolve(gallery);
        });
      });
    }

    constructor(pictures) {
      this._pictures = pictures;
      this._view = null;
    }

    get view() {
      return this._view;
    }

    show() {
      let markup = Twig.twig({ref: "twig-gallery"}).render({pictures: this.pictures});
      let compiled = $(markup);

      this._view = compiled[0];

      compiled.appendTo(document.body);

      compiled.find("a").on("click", (event) => {
        event.preventDefault();
        this.showImage(event.currentTarget.dataset.picture);
      });

      this.showImage(0);
    }

    showImage(index) {
      let viewport = $(this._view).find(".viewport").empty();
      let picture = this._pictures[index];

      $("<img/>").attr("src", picture.files.large).attr("alt", picture.name).appendTo(viewport);

      let thumbs = $(this._view).find(".thumb");
      thumbs.removeClass("active");

      $(thumbs.get(index)).addClass("active");

      if (picture.description) {
        $("<figcaption/>").text(picture.description).appendTo(viewport);
      }

      viewport.css({
        backgroundImage: 'url("' + picture.files.large + '")'
      });
    }

    get pictures() {
      return this._pictures;
    }
  }

  $.fn.kifiGallery = function() {
    return this.each((i, button) => {
      $(button).on("click", (event) => {
        Gallery.launch().then(gallery => {

          $(gallery.view).modal()
          .on("show.bs.modal", (event) => {
            gallery.view.focus();
          })
          .on("hide.bs.modal", (event) => {
            $(gallery.view).remove();
            button.focus();
          });
        });
      });
    });
  }

  return Gallery;
})(jQuery, Twig);

export { Gallery };
