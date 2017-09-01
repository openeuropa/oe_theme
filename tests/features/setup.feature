@api
Feature: Setup

  Scenario: Test that Behat can access a Drupal working copy.
    Given I am not logged in
    When I visit "/user"

    Then I should see "Username"
    And I should see "Password"
    And I should see an ".ecl-footer" element
