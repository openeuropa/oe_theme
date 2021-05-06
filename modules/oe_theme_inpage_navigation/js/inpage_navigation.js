/**
 * @file
 * ECL inpage navigation initializer.
 */
(function (ECL, Drupal, $) {

  Drupal.theme.oe_theme_inpage_navigation_item = function (id, title) {
    return '<li class="ecl-inpage-navigation__item"><a href="#' + id + '" class="ecl-link ecl-link--standalone ecl-inpage-navigation__link" data-ecl-inpage-navigation-link="">' + title + '</a></li>';
  }

  Drupal.behaviors.eclInPageNavigation = {
    attach: function attach(context, settings) {

      // Reused solution introduced in following repo: https://github.com/markedjs/marked/blob/master/src/Slugger.js.
      // Unfortunately, this javascript code can't be used as a module, that is why this code was incorporated manually.
      var seenIds = {};
      function slug (value) {
        var originalSlug = value
          .toLowerCase()
          .trim()
          // remove html tags
          .replace(/<[!\/a-z].*?>/ig, '')
          // remove unwanted chars
          .replace(/[\u2000-\u206F\u2E00-\u2E7F\\'!"#$%&()*+,./:;<=>?@[\]^`{|}~]/g, '')
          .replace(/\s/g, '-');

        var slug = originalSlug;
        var occurenceAccumulator = 0;
        if (seenIds.hasOwnProperty(slug)) {
          occurenceAccumulator = seenIds[originalSlug];
          do {
            occurenceAccumulator++;
            slug = originalSlug + '-' + occurenceAccumulator;
          } while (seenIds.hasOwnProperty(slug));
        }
        seenIds[originalSlug] = occurenceAccumulator;
        seenIds[slug] = 0;
        return slug;
      }

      // Loop through all the elements marked as source areas.
      Array.prototype.forEach.call(document.querySelectorAll('[data-inpage-navigation-source-area]'), function (area) {
        var selectors = area.getAttribute('data-inpage-navigation-source-area');

        // Loop through all the elements that are referenced by the specified selector(s), and mark them as source
        // elements. We cannot collect the elements at this stage, as multiple nested areas can be present in the page.
        // This could lead to scenarios where elements are collected multiple times, or not collected following the
        // order of appearance in the page.
        // The :scope pseudo-class is needed to make sure that the selectors are applied inside the parent.
        // @see https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelectorAll#user_notes
        Array.prototype.forEach.call(area.querySelectorAll(':scope ' + selectors), function (element) {
          element.setAttribute('data-inpage-navigation-source-element', '');
        });
      });

      var items_markup = '';
      // Collect all the elements marked as source. Now the elements will be unique and ordered correctly.
      Array.prototype.forEach.call(document.querySelectorAll('[data-inpage-navigation-source-element]'), function (element) {
        var title = element.textContent.trim();

        // Generate an unique ID if not present.
        if (!element.hasAttribute('id')) {
          element.setAttribute('id', slug(title));
        }

        // Cleanup the markup from the helper attribute added above.
        element.removeAttribute('data-inpage-navigation-source-element');

        items_markup += Drupal.theme('oe_theme_inpage_navigation_item', element.getAttribute('id'), title);
      });

      Array.prototype.forEach.call(document.querySelectorAll('[data-ecl-inpage-navigation]'), function (block) {
        if (items_markup.length === 0) {
          // @todo Replace within follow-up ticket to make adjusting of layout more flexible for different templates.
          // Adjust layout if we going to remove in-page element.
          $(block).closest('.ecl-col-lg-3').next('.ecl-col-lg-9').removeClass('ecl-col-lg-9').addClass('ecl-col-lg-12')
          // Remove in-page navigation element if we don't have links inside.
          block.remove();
          return;
        }
        block.querySelector('ul').innerHTML = items_markup;
      })
    },
  };
})(ECL, Drupal, jQuery);
