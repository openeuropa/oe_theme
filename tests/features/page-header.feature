@api @ecl2
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

  @run
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

  Scenario: Page content type has custom metadata shown in the page header.
    Given "oe_page" content:
      | title   | oe_summary                           | oe_content_content_owner                                                |
      | My page | http://www.example.org is a web page | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |
    And I am an anonymous user
    When I go to the "My page" page
    Then I should see the text "http://www.example.org is a web page" in the "page header intro"
    # The default text format should be applied, converting URLs into links.
    And I should see the link "http://www.example.org" in the "page header intro"

  Scenario: News content type has custom metadata shown in the page header.
    Given "oe_news" content:
      | title        | oe_summary                           | oe_teaser | body    | oe_publication_date | oe_subject                     | oe_author                                                               | oe_content_content_owner                                                |
      | My news item | http://www.example.org is a web page | My teaser | My body | 2019-04-02          | http://data.europa.eu/uxp/1000 | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |
    And I am an anonymous user
    When I go to the "My news item" page
    Then I should see the text "http://www.example.org is a web page" in the "page header intro"
    # The default text format should be applied, converting URLs into links.
    And I should see the link "http://www.example.org" in the "page header intro"
    And I should see "News" in the "page header meta"
    And I should see "02 April 2019" in the "page header meta"

  Scenario: Policy content type has custom metadata shown in the page header.
    Given "oe_policy" content:
      | title     | oe_summary                           | oe_content_content_owner                                                |
      | My Policy | http://www.example.org is a web page | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |
    And I am an anonymous user
    When I go to the "My Policy" page
    Then I should see the text "http://www.example.org is a web page" in the "page header intro"
    # The default text format should be applied, converting URLs into links.
    And I should see the link "http://www.example.org" in the "page header intro"

  Scenario: Publication content type has custom metadata shown in the page header.
    Given "oe_publication" content:
      | title          | oe_summary                           | oe_teaser | body    | oe_publication_date | oe_subject                     | oe_author                                                               | oe_content_content_owner                                                |
      | My publication | http://www.example.org is a web page | My teaser | My body | 2019-04-02          | http://data.europa.eu/uxp/1000 | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |
    And I am an anonymous user
    When I go to the "My publication" page
    Then I should see the text "http://www.example.org is a web page" in the "page header intro"
    # The default text format should be applied, converting URLs into links.
    And I should see the link "http://www.example.org" in the "page header intro"
    And I should see "02 April 2019" in the "page header meta"

  Scenario: The page header block shows the content language switcher.
    Given the following "Spanish" translation for the "Robots are everywhere" demo page:
      | Title | Los robots estan en todas partes |
    And  I am an anonymous user
    When I visit the "Spanish" translation page for the "Robots are everywhere" demo page
    Then I should see the heading "Los robots estan en todas partes" in the "page header"
    And I should not see the link "español" in the "page header" region

    When I visit the "French" translation page for the "Robots are everywhere" demo page
    Then I should see the heading "Robots are everywhere" in the "page header"
    # @todo Will be tested on OPENEUROPA-2013.
    # And I should see "français" in the "unavailable languages in the language page switcher"
    # And I should see "English" in the "selected language in the language page switcher"
    # And I should see the link "español" in the "language page switcher"
