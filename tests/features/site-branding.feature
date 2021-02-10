@api
Feature: Site branding
  In order to have my site branded with the official European Commission visual identity
  As a product owner
  I want to make sure that all necessary site branding features are provided by the OpenEuropa Theme.

  @javascript @enable-non-eu-language
  Scenario Outline: The European Commission logo is available throughout the site.
    Given I am on the homepage
    When the theme is configured to use the "European Union" style
    And I reload the page
    Then I should see the "logo" element in the "header"
    And the "English" EU mobile logo should be available
    When I open the language switcher dialog
    And I click "<language_selector>"
    Then the "<language>" EU mobile logo should be available
    When the theme is configured to use the "European Commission" style
    And I reload the page
    Then I should see the "logo" element in the "header"
    When I am on "the user registration page"
    Then I should see the "logo" element in the "header"

    Examples:
      | language_selector       | language   |
      | български               | Bulgarian  |
      | čeština                 | Czech      |
      | dansk                   | Danish     |
      | Deutsch                 | German     |
      | eesti                   | Estonian   |
      | ελληνικά                | Greek      |
      | español                 | Spanish    |
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
      | Icelandic               | English    |

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
