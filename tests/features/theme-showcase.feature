@api @demo
Feature: Theme showcase
  In order to be able to showcase the theme and its features
  As a developer
  I want to make sure that I can access a demo site.

  Scenario: Footer features placeholder blocks
    Given I am on the homepage
    Then I should see the "identity block" element in the "identity footer"
    And I should see the "corporate block" element in the "corporate footer"
    And I should see the "contacts block" element in the "contacts footer"
