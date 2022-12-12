@api
Feature: Corporate blocks feature
  In order to be able to showcase Corporate blocks
  As an anonymous user
  I want see all Corporate blocks on all pages

  Scenario Outline: The corporate blocks are available throughout the site.
    Given I am an anonymous user
    When I am on "<page>"
    Then I should see the "search form" element in the "header"

    Examples:
      | page                        |
      | the home page               |
      | the user registration page  |

  Scenario Outline: By default the European Commission footer is displayed.
    Given I am an anonymous user
    When I am on "<page>"
    Then I should see the European Commission footer with link "https://commission.europa.eu/index_en"

    Examples:
      | page                        |
      | the home page               |
      | the user registration page  |

  Scenario Outline: Changing the site's style will display either the European Commission or the European Union footer.
    Given I am an anonymous user
    When I am on "<page>"
    Then I should see the European Commission footer with link "https://commission.europa.eu/index_en"
    And I should not see the "European Union" footer

    When the theme is configured to use the "European Union" style
    And I reload the page
    Then I should not see the "European Commission" footer
    But I should see the European Union footer with link "https://european-union.europa.eu/index_en" label "Home - European Union" image alt "European Union flag" title "European Union"

    When the theme is configured to use the "European Commission" style
    And I reload the page
    Then I should not see the "European Union" footer
    But I should see the European Commission footer with link "https://commission.europa.eu/index_en"

    Examples:
      | page                        |
      | the home page               |
      | the user registration page  |
