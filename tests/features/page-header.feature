@api
Feature: Page header block component.
  In order to better understand the context of the page
  As a site user
  I want to see a page header with useful information.

  Background:
    Given the following demo pages:
      | title                               | body                             |
      | Robots are everywhere               | They are part of our daily life. |
      | The benefits of ergonomic equipment | Take care of your work tools.    |

  Scenario Outline: The page header block shows the current page metadata.
    Given I am an anonymous user
    When I go to the "<page>" demo page
    Then I should see the heading "<page>" in the "page header"
    And the breadcrumb trail should be "Home"
    And the breadcrumb active element should be "<page>"

    Examples:
      | page                                |
      | Robots are everywhere               |
      | The benefits of ergonomic equipment |

  Scenario: The standard title is shown on other pages.
    Given I am an anonymous user
    When I am on "the user registration page"
    Then I should see the heading "Create new account" in the "page header"
    And I should see "OpenEuropa" in the "page header site identity"
    And the breadcrumb trail should be "Home"
    And the breadcrumb active element should be "Create new account"

  Scenario: Change the title to verify that the page header is updated accordingly.
    Given I am logged in as a user that can "edit any" demo pages
    When I go to the "Robots are everywhere" demo page
    And I click "Edit"
    And I fill in "Title" with "Robots are everywhere nowadays"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I press "Save"
    Then I should see the heading "Robots are everywhere nowadays" in the "page header"
    And the breadcrumb trail should be "Home"
    And the breadcrumb active element should be "Robots are everywhere nowadays"

    Given I am an anonymous user
    When I go to the "Robots are everywhere nowadays" demo page
    Then I should see the heading "Robots are everywhere nowadays" in the "page header"
    And the breadcrumb trail should be "Home"
    And the breadcrumb active element should be "Robots are everywhere nowadays"
