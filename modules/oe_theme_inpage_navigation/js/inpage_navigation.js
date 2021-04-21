/**
 * @file
 * ECL inpage navigation initializer.
 */
(function (ECL, Drupal, $) {
  Drupal.behaviors.eclInPageNavigation = {
    attach: function attach(context, settings) {
      // List of heading selectors.
      const selectors = [];
      // Collect heading selectors.
      document.querySelectorAll('[data-inpage-navigation-source-area]').forEach(function (element) {
        let header_selector = element.getAttribute('data-inpage-navigation-source-area');
        selectors.push('[data-inpage-navigation-source-area="' + header_selector + '"] ' + header_selector);
      })
      let headers_selector = selectors.join(', ');
      // Run query on all required selectors.
      const headers = document.querySelectorAll(headers_selector);
      const li_html = [];
      for (let h = 0; h < headers.length; h++) {
        let title = headers[h].textContent.trim();
        if (!headers[h].hasAttribute('id')) {
          // Set id if not available yet in h2 tag.
          headers[h].setAttribute('id', title.replace(/\W/g,'-').toLowerCase())
        }
        let id = $(headers[h]).uniqueId().attr('id');
        li_html.push('<li class="ecl-inpage-navigation__item"><a href="#' + id + '" class="ecl-link ecl-link--standalone ecl-inpage-navigation__link" data-ecl-inpage-navigation-link="">' + title + '</a></li>');
      }

      // In-page navigation blocks.
      const elements = document.querySelectorAll('[data-ecl-inpage-navigation]');
      for (let i = 0; i < elements.length; i++) {
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
