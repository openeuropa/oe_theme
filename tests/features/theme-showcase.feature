@api @demo
Feature: Theme showcase
  In order to be able to showcase the theme and its features
  As a developer
  I want to make sure that I can setup a demo site.

  Scenario: The demo site header features placeholder blocks
    Given I am on the homepage
    Then I should see the "sites switcher" element in the "header"
    And I should see the "search box" element in the "header"
    And I should see the "language switcher" element in the "header"

  Scenario: The demo site footer features placeholder blocks
    Given I am on the homepage
    Then I should see the "identity block" element in the "identity footer"
    And I should see the "corporate block" element in the "corporate footer"
    And I should see the "contacts block" element in the "contacts footer"
