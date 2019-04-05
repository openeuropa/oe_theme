@api
Feature: News content type.
  As a user
  I want to access the content of a news
  So I can find the information I'm looking for.

  @run
  Scenario: News information is shown in teasers.
    Given "oe_news" content:
      | title           | oe_news_summary | oe_news_teaser | body      | oe_news_publication_date | oe_news_subject                | oe_news_author                                                          | oe_content_content_owner                                                |
      | Full news title | Short summary   | News teaser    | News body | 1554197428               | http://data.europa.eu/uxp/1000 | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH | http://publications.europa.eu/resource/authority/corporate-body/EP_PECH |

    When I am on "recent content page"
    Then I should see the heading "Full news title"
    And I should see the text "News teaser"
    And I should see the text "02 April 2019"
    But I should not see the text "Short summary"
    And I should not see the text "News body"
    # Test that no image is shown.

    And I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media" permission
    # Create a "Media AV portal photo".
    When I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15"
    And I press "Save"
    # Attach the media to the news.
    When I go to the "Full news title" page
    And I click "Edit"
    And I fill in "Use existing media" with "Euro with miniature figurines"
    # Introduce a short title.
    And I fill in "Short title" with "Shorter title"
    And I press "Save"

    When I am on "recent content page"
    Then I should see the heading "Shorter title"
    And I should see the text "News teaser"
    And I should see the text "02 April 2019"
    But I should not see the text "Full news title"
    And I should not see the text "Short summary"
    And I should not see the text "News body"
    # Test the image presence.
