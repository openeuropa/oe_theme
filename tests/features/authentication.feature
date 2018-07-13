@api
Feature: Authentication
  In order to allow personalized actions to be taken
  As a product owner
  I need to make sure that we can detect the user's authentication status in the frontend

  Scenario:
    # Behat Drupal Extension 4.x determines the logged in status by inspecting
    # the DOM. This will fail if the status cannot be derived from the frontend.
    Given I am logged in as a user with the "authenticated" role
