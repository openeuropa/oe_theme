@api @demo
Feature: Theme showcase
  In order to be able to showcase the theme and its features
  As a developer
  I want to make sure that I can setup a demo site with enabled more featured standardised branding.

  Scenario: The demo site header features placeholder blocks
    When I am on the homepage
    Then I should see the "logo" element in the "header"
    And I should see the "search form" element in the "header"
    And I should see the "language switcher" element in the "header"
    And I should see the "navigation menu" element in the "header"
    And I should see the "site name" element in the "header"

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

  @javascript
  Scenario: The demo site navigation menu features dropdown menus
    When I am on the homepage
    # We can't check the visibility of the submenus
    # because their position is absolute which gives us false positives.
    Then I should not visibly see the link "Commission at work"
    Then I should not visibly see the link "Democratic change"

    When I hover over the link "About"
    And the overlay "about dropdown menu" is visible
    Then I should see the link "Commission at work"
    But I should not visibly see the link "Democratic change"

    When I hover over the link "Priorities"
    And the overlay "priorities dropdown menu" is visible
    Then I should not visibly see the link "Commission at work"
    But I should see the link "Democratic change"

  @javascript
  Scenario: The dropdown component shows/hides on click event
    When I am on "the ECL dropdown component page"
    Then the "dropdown content" is not visible

    When I press "Dropdown"
    Then the "dropdown content" is visible

  @javascript
  Scenario: The language switcher dialog can be accessed
    When I am on the homepage
    Then the overlay "language switcher links" is not visible

    When I open the language switcher dialog
    Then the overlay "language switcher links" is visible

    And I should not see "EU official languages" in the "language switcher"
    And I should not see "Other languages" in the "language switcher"

    And I should see the following links in the "language switcher":
      | български   |
      | español     |
      | čeština     |
      | dansk       |
      | Deutsch     |
      | eesti       |
      | ελληνικά    |
      | English     |
      | français    |
      | Gaeilge     |
      | hrvatski    |
      | italiano    |
      | latviešu    |
      | lietuvių    |
      | magyar      |
      | Malti       |
      | Nederlands  |
      | polski      |
      | português   |
      | română      |
      | slovenčina  |
      | slovenščina |
      | suomi       |
      | svenska     |

    And the active language switcher link in the dialog is "English"

    When I click "polski" in the "language switcher"
    Then the url should match "/pl"

    When I open the language switcher dialog
    Then the active language switcher link in the dialog is "polski"

    When I press "Close"
    Then the "language switcher links" is not visible

  @javascript
  Scenario: Site visitor can change language using the language switcher
    Given the theme is configured to use the "European Union" style
    When I am on the homepage
    Then the "language switcher link" element should contain "English"

    When I open the language switcher dialog
    And I click "polski"
    Then the url should match "/pl"
    And the "language switcher link" element should contain "polski"

  Scenario: Site visitors can access the ECL components overview page
    When I am on the homepage
    And I click "Components" in the "navigation" region
    Then I should be on "the ECL components overview page"
    And I should see the heading "Components" in the "page header"

  Scenario: Changing the ECL branding will display site header with Core or Standardised style.
    Given I am an anonymous user
    When the theme is configured to use the "Standardised" ECL branding
    When I am on "<page>"
    Then I should see the "Standardised" site header
    And I should see the "navigation menu" element in the "header with menu"

    When the theme is configured to use the "Core" ECL branding
    And I reload the page
    Then I should see the "Core" site header
    And I should see the "navigation menu" element in the "header with menu"
