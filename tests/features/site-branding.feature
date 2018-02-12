@api
Feature: Setup
  In order to have my site branded with the official European Commission visual identity
  As a product owner
  I want to make sure that all necessary site branding features are provided by the OpenEuropa Theme.

  Scenario: The European Commission logo is available throughout the site
    Given I am on the homepage
    Then I should see the "logo" element in the "header"
    And I am on "the login page"
    Then I should see the "logo" element in the "header"

  Scenario: The breadcrumb is visible everywhere but on the homepage
    Given I am on the homepage
    Then I should not see the "breadcrumb" element in the "page"
    And I am on "the login page"
    Then I should see the "breadcrumb" element in the "page"
