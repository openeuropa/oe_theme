@api
Feature: Navigation
  @javascript
  Scenario: Test that navigation elements use ECL components.
    Given I am logged in as a user with the "administrator" role
    When I visit "/node/add/page"
    And I fill in "Title" with "Test"
    And I click "Save"
    Then I should be on "node/1"
    And I should see "Test"
    And I should see an ".ecl-navigation-list--tabs" element
