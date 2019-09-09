@api @ecl2
Feature: Corporate blocks feature
  In order to be able to showcase Corporate blocks
  As an anonymous user
  I want see all Corporate blocks on all pages

  Scenario Outline: The corporate blocks are available throughout the site
    Given I am an anonymous user
    When I am on "<page>"
    Then I should see the "search form" element in the "header"
    # @todo: Should be added on the scope of OPENEUROPA-1992
    # And I should see the "sites switcher" element in the "header"

    Examples:
      | page                        |
      | the homepage                |
      | the user registration page  |

  Scenario Outline: The demo site footer features placeholder blocks
    Given I am an anonymous user
    When I am on "<page>"
    Then I should see "European Commission" in the "footer" element
    And I should see "Follow the European Commission" in the "footer" element
    And I should see "European Union" in the "footer" element

    Examples:
      | page                        |
      | the homepage                |
      | the user registration page  |
