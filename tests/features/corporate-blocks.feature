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
    Then I should see "European Commission" footer

    Examples:
      | page                        |
      | the home page               |
      | the user registration page  |

  Scenario: The European Commission footer or the European Union one si shown depending on the which style is chosen.
    Given I am an anonymous user
    When I am on "the home page"
    Then I should see "European Commission" footer
    And I should not see "European Union" footer

    When the theme is configured to use the "European Union" style
    When I am on "the user registration page"
    Then I should not see "European Commission" footer
    But I should see "European Union" footer instead

    When the theme is configured to use the "European Commission" style
    When I am on "the home page"
    Then I should not see "European Union" footer
    But I should see "European Commission" footer instead
