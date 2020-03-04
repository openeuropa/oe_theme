@api
Feature: News content type.
  As a user
  I want to access the content of a news
  So I can find the information I'm looking for.

  Scenario: News information is shown in teasers.
    Given I am logged in as a user with the "create oe_news content, access content, edit any oe_news content, view published skos concept entities" permission
    And "oe_news" content:
      | title           | oe_summary    | oe_teaser   | body      | oe_publication_date | oe_subject                     | oe_author                                                               | oe_content_content_owner                                                |
      | Full news title | Short summary | News teaser | News body | 2019-04-02          | http://data.europa.eu/uxp/1000 | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |
    And the following images:
      | name       | file           |
      | My Image 1 | example_1.jpeg |

    When I am on "the recent content page"
    Then I should see the link "Full news title"
    And I should see the text "News teaser"
    And I should see the text "02 April 2019"
    But I should not see the text "Short summary"
    And I should not see the text "News body"
    And I should not see an "list item image" element

    # Attach the media to the news.
    When I go to the "Full news title" page
    And I click "Edit"
    And I fill in "Use existing media" with "My Image 1"
    # Introduce a short title.
    And I fill in "Alternative title" with "Shorter title"
    And I press "Save"

    When I am on "the recent content page"
    Then I should see the link "Shorter title"
    And I should see the text "News teaser"
    And I should see the text "02 April 2019"
    But I should not see the text "Full news title"
    And I should not see the text "Short summary"
    And I should not see the text "News body"
    And I should see a "list item image" element
    And the "list item" element should contain "example_1.jpeg"
