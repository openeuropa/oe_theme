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

  Scenario: The demo site navigation features placeholder menu links
    Given I am on the homepage
    Then I should see the following links in the "navigation" region:
      | About      |
      | Priorities |
      | Contacts   |
    And I should see the following links in the "priorities dropdown menu":
      | Democratic change              |
      | Digital single market          |
      | Energy union and climate       |
      | Internal market                |
      | Jobs, growth and investment    |
      | Justice and fundamental rights |
      | Migration                      |
      | Monetary union                 |
    And I should see the following links in the "about dropdown menu":
      | Commission at work |
      | Departments        |

  Scenario: The demo site footer features placeholder blocks
    Given I am on the homepage
    Then I should see the "identity block" element in the "identity footer"
    And I should see the "corporate block" element in the "corporate footer"
    And I should see the "contacts block" element in the "contacts footer"

  @javascript
  Scenario: The language switcher dialog can be accessed
    Given I am on the homepage
    Then the "language switcher overlay" is not visible

    When I open the language switcher dialog
    Then the "language switcher overlay" is visible

    When I press "Close"
    Then the "language switcher overlay" is not visible

  @javascript
  Scenario: Site visitor can change language using the language switcher
    Given I am on the homepage
    Then the "language switcher link" element should contain "English"

    When I open the language switcher dialog
    And I click "Polish"
    Then the url should match "/pl"
    And the "language switcher link" element should contain "Polish"
