@api @demo
Feature: Theme showcase
  In order to be able to showcase the theme and its features
  As a developer
  I want to make sure that I can setup a demo site.

  Scenario: The demo site header features placeholder blocks
    When I am on the homepage
    Then I should see the "sites switcher" element in the "header"
    And I should see the "search box" element in the "header"
    And I should see the "language switcher" element in the "header"

  Scenario: The demo site navigation features placeholder menu links
    When I am on the homepage
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
    When I am on the homepage
    Then I should see the "custom block" element in the "custom footer"
    And I should see the "corporate top block" element in the "corporate top footer"
    And I should see the "corporate bottom block" element in the "corporate bottom footer"

  @javascript
  Scenario: The language switcher dialog can be accessed
    When I am on the homepage
    Then the "language switcher overlay" is not visible

    When I open the language switcher dialog
    Then the "language switcher overlay" is visible

    When I press "Close"
    Then the "language switcher overlay" is not visible

  @javascript
  Scenario: Site visitor can change language using the language switcher
    When I am on the homepage
    Then the "language switcher link" element should contain "English"

    When I open the language switcher dialog
    And I click "Polski"
    Then the url should match "/pl"
    And the "language switcher link" element should contain "Polski"

  Scenario: Site visitors can access the ECL components overview page
    When I am on the homepage
    And I click "Components" in the "navigation" region
    Then I should be on "the ECL components overview page"
    And I should see the heading "Components" in the "page header"
