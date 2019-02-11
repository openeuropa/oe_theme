@api
Feature: Search form block
  In order to use search feature
  As a site visitor
  I want see search form block on page header area.

  @oe_search
  Scenario: The European Commission search form block is available throughout the site
    Given I am on the homepage
    Then I should see the "search form" element in the "header"
    When I fill in "Search" with "European Commission"
    And I press "Search"
    Then I should be redirected to "https://ec.europa.eu/search/?QueryText=European%20Commission&swlang=en"


