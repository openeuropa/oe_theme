@api
Feature: Setup

  Scenario: Test that Behat can access a Drupal working copy.
    Given I am not logged in
    When I visit "/user"

    Then I should see "Username"
    When I enter "test" for "Username"
    And I enter "pass" for "Password"
    And I press the "Log in" button
