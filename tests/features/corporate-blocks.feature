@api
Feature: Corporate blocks feature
  In order to be able to showcase Corporate blocks
  As an anonymous user
  I want see a search form block on the page header area.
  I want to see site switcher block on all pages
  I want to see footer blocks on all pages

  Scenario Outline: The corporate blocks are available throughout the site
    Given I am an anonymous user
    When I am on "<page>"
    Then I should see the "search form" element in the "header"
    And I should see the "sites switcher" element in the "header"

    Examples:
      | page                        |
      | the homepage                |
      | the user registration page  |

  @ecl2
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
