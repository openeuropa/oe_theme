@api
Feature: Corporate blocks feature
  In order to be able to showcase Corporate blocks
  As an anonymous user
  I want see a search form block on the page header area.
  I want to see footer blocks on all pages
  I want to see site switcher block on all pages

  Scenario Outline: The corporate blocks are available throughout the site
    Given I am an anonymous user
    When I am on "<page>"
    Then I should see the "search form" element in the "header"
    And I should see the "sites switcher" element in the "header"
    And I should see the "corporate footer" element in the "footer"

    Examples:
      | page                        |
      | the homepage                |
      | the user registration page  |
