@api
Feature: News content type.
  As a user
  I want to access the content of a news
  So I can find the information I'm looking for.

  @cleanup:media @cleanup:file
  Scenario: News information is shown in teasers.
    Given "oe_news" content:
      | title           | oe_news_summary | oe_news_teaser | body      | oe_news_publication_date | oe_news_subject                | oe_news_author                                                          | oe_content_content_owner                                                |
      | Full news title | Short summary   | News teaser    | News body | 2019-04-02               | http://data.europa.eu/uxp/1000 | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |

    When I am on "the recent content page"
    Then I should see the heading "Full news title"
    And I should see the text "News teaser"
    And I should see the text "02 April 2019"
    But I should not see the text "Short summary"
    And I should not see the text "News body"
    And I should not see an "list item image" element

    And I am logged in as a user with the "create oe_news content, access content, edit any oe_news content, view published skos concept entities, create image media" permission
    # Create a "Media AV portal photo".
    When I go to "the image creation page"
    When I fill in "Name" with "My Image 1"
    And I attach the file "example_1.jpeg" to "Image"
    And I press "Upload"
    And I fill in "Alternative text" with "Image Alt Text 1"
    And I press "Save"

    # Attach the media to the news.
    When I go to the "Full news title" page
    And I click "Edit"
    And I fill in "Use existing media" with "My Image 1"
    # Introduce a short title.
    And I fill in "Short title" with "Shorter title"
    And I press "Save"

    When I am on "the recent content page"
    Then I should see the heading "Shorter title"
    And I should see the text "News teaser"
    And I should see the text "02 April 2019"
    But I should not see the text "Full news title"
    And I should not see the text "Short summary"
    And I should not see the text "News body"
    And I should see a "list item image" element
    And the "list item image" element should contain "example_1.jpeg"
