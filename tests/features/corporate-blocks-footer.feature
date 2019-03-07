@api
Feature: Corporate blocks feature
  In order to be able to showcase Corporate blocks
  As an anonymous user
  I want to see footer blocks on all pages

  Scenario: The European Commission footer block is available throughout the site
    Given I am on the homepage
    Then I should see the "corporate footer" element in the "footer"
    When I am on "the user registration page"
    Then I should see the "corporate footer" element in the "footer"
