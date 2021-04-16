/**
 * @file
 * ECL inpage navigation initializer.
 */
(function (ECL, Drupal, $) {
  Drupal.behaviors.eclInPageNavigation = {
    attach: function attach(context, settings) {
      // In-page navigation blocks.
      var elements = document.querySelectorAll('[data-ecl-inpage-navigation]');
      // List of headings inside source element of in-page navigation.
      var headers = document.querySelectorAll('div[data-inpage-navigation-source-area] h2.ecl-u-type-heading-2');
      li_html = [];
      for (var h = 0; h < headers.length; h++) {
        title = headers[h].innerHTML;
        if (!headers[h].hasAttribute('id')) {
          // Set id if not available yet in h2 tag.
          headers[h].setAttribute('id', title.replace(/\W/g,'-').toLowerCase())
        }
        id = $(headers[h]).uniqueId().attr('id');
        li_html.push('<li class="ecl-inpage-navigation__item"><a href="#' + id + '" class="ecl-link ecl-link--standalone ecl-inpage-navigation__link" data-ecl-inpage-navigation-link="">' + title + '</a></li>');
      }

      for (var i = 0; i < elements.length; i++) {
        if (li_html.length === 0) {
          // Adjust layout if we going to remove in-page element.
          $(elements[i]).closest('.ecl-col-lg-3').next('.ecl-col-lg-9').removeClass('ecl-col-lg-9').addClass('ecl-col-lg-12')
          // Remove in-page navigation element if we don't have links inside.
          elements[i].remove();
          continue;
        }
        elements[i].querySelector('ul').innerHTML = li_html.join(' ');
      }
    }
  };
})(ECL, Drupal, jQuery);
