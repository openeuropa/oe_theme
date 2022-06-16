@api
Feature: Page header block component.
  In order to better understand the context of the page
  As a site user
  I want to see a page header with useful information.

  Background:
    Given the following demo pages:
      | title                               | path       | oe_content_content_owner                                                |
      | Robots are everywhere               | /robots    | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |
      | The benefits of ergonomic equipment | /ergonomic | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |

  Scenario Outline: The page header block shows the current page metadata.
    Given I am an anonymous user
    When I go to the "<page>" page
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
    And I should not see the "page header site identity" element in the "page"
    And the breadcrumb trail should be "Home"
    And the breadcrumb active element should be "Create new account"

  Scenario: Change the title to verify that the page header is updated accordingly.
    Given I am logged in as a user that can "edit any" demo pages
    When I go to the "Robots are everywhere" page
    And I click "Edit"
    And I fill in "Title" with "Robots are everywhere nowadays"
    And I press "Save"
    Then I should see the heading "Robots are everywhere nowadays" in the "page header"
    And the breadcrumb trail should be "Home"
    And the breadcrumb active element should be "Robots are everywhere nowadays"

    Given I am an anonymous user
    When I go to the "Robots are everywhere nowadays" page
    Then I should see the heading "Robots are everywhere nowadays" in the "page header"
    And the breadcrumb trail should be "Home"
    And the breadcrumb active element should be "Robots are everywhere nowadays"

  @remove-autop-plain-text
  Scenario: Page content type has custom metadata shown in the page header.
    Given "oe_page" content:
      | title   | oe_summary                           | oe_content_content_owner                                                |
      | My page | http://www.example.org is a web page | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |
    And I am an anonymous user
    When I go to the "My page" page
    Then I should see the text "http://www.example.org is a web page" in the "page header intro"
    # The default text format should be applied, converting URLs into links.
    And I should see the link "http://www.example.org" in the "page header intro"

  @remove-autop-plain-text
  Scenario: Policy content type has custom metadata shown in the page header.
    Given "oe_policy" content:
      | title     | oe_summary                           | oe_content_content_owner                                                |
      | My Policy | http://www.example.org is a web page | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |
    And I am an anonymous user
    When I go to the "My Policy" page
    Then I should see the text "http://www.example.org is a web page" in the "page header intro"
    # The default text format should be applied, converting URLs into links.
    And I should see the link "http://www.example.org" in the "page header intro"
