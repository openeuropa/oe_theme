@api
Feature: Search form block
  In order to use the search feature
  As a site visitor
  I want see a search form block on the page header area.

  Scenario: The European Commission search form block is available throughout the site
    Given I am on the homepage
    Then I should see the "search form" element in the "header"
    When I am on "the user registration page"
    Then I should see the "search form" element in the "header"
