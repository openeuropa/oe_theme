/**
 * @file
 * ECL inpage navigation initializer.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclInPageNavigation = {
    attach: function attach(context, settings) {
      var elements = document.querySelectorAll('[data-ecl-inpage-navigation]');
      var headers = document.querySelectorAll('h2.ecl-u-type-heading-2');
      li_html = [];
      for (var h = 0; h < headers.length; h++) {
        id = headers[h].getAttribute('id');
        title = headers[h].innerHTML;
        li_html.push('<li class="ecl-inpage-navigation__item"><a href="#' + id + '" class="ecl-link ecl-link--standalone ecl-inpage-navigation__link" data-ecl-inpage-navigation-link="">' + title + '</a></li>');
      }
      for (var i = 0; i < elements.length; i++) {
        elements[i].querySelector('ul').innerHTML = li_html.join(' ');
        var inpage_navigation = new ECL.InpageNavigation(elements[i]);
        inpage_navigation.init();
      }
    }
  };
})(ECL, Drupal);
