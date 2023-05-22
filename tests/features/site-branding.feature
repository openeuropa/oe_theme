@api
Feature: Site branding
  In order to have my site branded with the official European Commission visual identity
  As a product owner
  I want to make sure that all necessary site branding features are provided by the OpenEuropa Theme.

  # @todo Test should be rewritten since it tests European Union style logo on the English homepage and European
  # Commision style logo on the English registration page for each iteration.
  @javascript @enable-non-eu-language
  Scenario Outline: The European Commission logo is available throughout the site.
    Given I am on the homepage
    When the theme is configured to use the "European Union" style
    And I reload the page
    Then I should see the "logo" element in the "header"
    When I open the language switcher dialog
    And I click "<language_selector>"
    Then the "<language>" EU mobile and desktop logos should be available
    When the theme is configured to use the "European Commission" style
    And I reload the page
    Then I should see the "logo" element in the "header"
    When I am on "the user registration page"
    Then I should see the "logo" element in the "header"

    Examples:
      | language_selector       | language   |
      | English                 | English    |
      | български               | Bulgarian  |
      | español                 | Spanish    |
      | čeština                 | Czech      |
      | dansk                   | Danish     |
      | Deutsch                 | German     |
      | eesti                   | Estonian   |
      | ελληνικά                | Greek      |
      | français                | French     |
      | Gaeilge                 | Irish      |
      | hrvatski                | Croatian   |
      | italiano                | Italian    |
      | latviešu                | Latvian    |
      | lietuvių                | Lithuanian |
      | magyar                  | Hungarian  |
      | Malti                   | Maltese    |
      | Nederlands              | Dutch      |
      | polski                  | Polish     |
      | português               | Portuguese |
      | română                  | Romanian   |
      | slovenčina              | Slovak     |
      | slovenščina             | Slovenian  |
      | suomi                   | Finnish    |
      | svenska                 | Swedish    |
      # Non-EU language.
      | Icelandic               | Icelandic  |

  # todo We can check footer logo as a part of another test with non-eu language.
  @javascript @enable-non-eu-language
  Scenario: The European Union logo is available in the footer when non-EU language is selected.
    Given I am on the homepage
    When the theme is configured to use the "European Union" style
    And I reload the page
    Then I should see the "footer logo" element in the "footer"
    When I open the language switcher dialog
    And I click "Icelandic"
    Then I should see the "footer logo" element in the "footer"

  Scenario: The breadcrumb is visible everywhere.
    When I am on the homepage
    Then I should see the "breadcrumb" element in the "page"
    When I am on "the user registration page"
    Then I should see the "breadcrumb" element in the "page"

  Scenario: The page header is visible everywhere.
    When I am on the homepage
    Then I should see the "page header" element in the "page"
    When I am on "the user registration page"
    Then I should see the "page header" element in the "page"

  @javascript @enable-non-eu-language
  Scenario Outline: The European Union header logo has accessibility attributes.
    Given I am on the homepage
    And the theme is configured to use the "European Union" style
    And I reload the page
    When I open the language switcher dialog
    And I click "<language_selector>"
    Then the header logo should contain accessibility attributes with link "<link>" label "<label>" alt "<alt>" title "<title>"
    When I am on "the user registration page"
    And I open the language switcher dialog
    And I click "<language_selector>"
    Then the header logo should contain accessibility attributes with link "<link>" label "<label>" alt "<alt>" title "<title>"

    Examples:
      | language_selector | link                                      | label                 | alt                 | title          |
      | English           | https://european-union.europa.eu/index_en | Home - European Union | European Union flag | European Union |
      | български         | https://european-union.europa.eu/index_bg | Home - European Union | European Union flag | European Union |
      # Non-EU language.
      | Icelandic         | https://european-union.europa.eu          | Home - European Union | European Union flag | European Union |

  @javascript @enable-non-eu-language
  Scenario Outline: The European Commission header logo has accessibility attributes.
    Given I am on the homepage
    And the theme is configured to use the "European Commission" style
    And I reload the page
    When I open the language switcher dialog
    And I click "<language_selector>"
    Then the header logo should contain accessibility attributes with link "<link>" label "<label>" alt "<alt>" title "<title>"
    When I am on "the user registration page"
    And I open the language switcher dialog
    And I click "<language_selector>"
    Then the header logo should contain accessibility attributes with link "<link>" label "<label>" alt "<alt>" title "<title>"

    Examples:
      | language_selector | link                                  | label                      | alt                      | title               |
      | English           | https://commission.europa.eu/index_en | Home - European Commission | European Commission logo | European Commission |
      | български         | https://commission.europa.eu/index_bg | Home - European Commission | European Commission logo | European Commission |
      # Non-EU language.
      | Icelandic         | https://commission.europa.eu          | Home - European Commission | European Commission logo | European Commission |
