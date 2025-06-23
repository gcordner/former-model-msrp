/**
 * All of the code for your public-facing JavaScript source
 * should reside in this file.
 *
 * Note: It has been assumed you will write jQuery code here, so the
 * $ function reference has been prepared for usage within the scope
 * of this function.
 *
 * This enables you to define handlers, for when the DOM is ready:
 *
 * $(function() {
 *
 * });
 *
 * When the window is loaded:
 *
 * $( window ).load(function() {
 *
 * });
 *
 * ...and/or other possibilities.
 *
 * Ideally, it is not considered best practise to attach more than a
 * single DOM-ready or window-load handler for a particular page.
 * Although scripts in the WordPress core, Plugins and Themes may be
 * practising this, we should strive to set a better example in our own work.
 */
(function ($) {
  "use strict";

  // DOM Ready
  $(function () {
    const $listPrice = $(
      '<p class="fm-msrp" style="font-size: 1rem; color: #888;"></p>'
    );
    $(".single_variation_wrap").prepend($listPrice);

    $("form.variations_form")
      .on("show_variation", function (event, variation) {
        console.log("Selected variation:", variation);

        if (variation.fm_msrp) {
          const formatted = wc_price_format(variation.fm_msrp);
          $listPrice.html("List Price: " + formatted).show();
        } else {
          $listPrice.hide();
        }
      })
      .on("hide_variation", function () {
        $listPrice.hide();
      });

    function wc_price_format(value) {
      const amount = Number(value);
      if (isNaN(amount)) return value;

      return (
        '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">&#36;</span>' +
        amount.toFixed(2) +
        "</bdi></span>"
      );
    }
  });
})(jQuery);
