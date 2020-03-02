@api @event @datetime_testing
Feature: Event content type.
  As a user
  I want to access the content of a event
  So I can find the information I'm looking for.

  Scenario: When an anonymous user visits and event page the registration button and page text should change according to the actual time.
    Given I am on the homepage
    And the date is "17 February 2020 2pm"
    And the following Event Content entity:
      | Title                   | My first event            |
      | Type                    | exhibitions               |
      | Description summary     | Event description summary |
      | Description             | Event description         |
      | Start date              | 2020-06-15T12:30:00       |
      | End date                | 2020-06-20T18:30:00       |
      | Registration start date | 2020-03-01T12:30:00       |
      | Registration end date   | 2020-03-10T18:30:00       |
      | Registration URL        | http://example.com        |
      | Summary for report      | Report summary            |
      | Report text             | Report text               |

    When I am visiting the "My first event" content
    Then I should see the heading "Description"
    And I should see the text "Event description summary"
    And I should see the text "Event description"
    And the registration button is not active
    And I should see "Registration will open in 1 week 5 days. You can register from 1 March 2020, 13:30, until 10 March 2020, 19:30."

    When the date is "05 March 2020 2pm"
    And I run cron
    And I reload the page
    Then I should see "Book your seat, 5 days left to register, registration will end on 10 March 2020, 19:30"
    And the registration button is active

    When the date is "21 June 2020 2pm"
    And I run cron
    And I reload the page
    Then I should see "Registration period ended on Tuesday 10 March 2020, 19:30"
    And the registration button is not active

    When the date is "15 March 2020 2pm"
    Then I should see the heading "Report"
    And I should not see the heading "Description"
    And I should see the text "Report summary"
    And I should see the text "Report text"
    And I should not see the text "Event description summary"
    And I should not see the text "Event description"

  Scenario: As an anonymous user, when I visit an event I can see the information in the correct layout
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create av_portal_photo media, manage corporate content entities, view the administration theme" permission
    And the date is "17 February 2019 2pm"
    And the following images:
      | name                          | file            |
      | Euro with miniature figurines | placeholder.png |
    And the following Default Venue entity:
      | Name     | Name of the venue                                                                         |
      | Address  | country_code: BE - locality: Brussel - address_line1: Rue belliard 28 - postal_code: 1000 |
      | Capacity | Capacity of the venue                                                                     |
      | Room     | Room of the venue                                                                         |
    And the following Press Contact entity:
      | Name         | Name of the press contact                                                                |
      | Address      | country_code: HU - locality: Szeged - address_line1: Press contact 1 - postal_code: 6700 |
      | Email        | press@example.com                                                                      |
      | Phone number | +32477777777                                                                             |
    And the following General Contact entity:
      | Name         | Name of the general contact                                                                  |
      | Address      | country_code: HU - locality: Budapest - address_line1: General contact 1 - postal_code: 1011 |
      | Email        | general@example.com                                                                          |
      | Phone number | +32477792933                                                                                 |
    And the following Event Content entity:
      | Title                   | Event demo page                                                 |
      | Type                    | exhibitions                                                     |
      | Description summary     | Description summary text                                        |
      | Description             | Event description                                               |
      | Summary for report      | Report summary                                                  |
      | Report text             | Report text                                                     |
      | Start date              | 2019-02-21T02:21:00                                             |
      | End date                | 2019-02-21T14:21:00                                             |
      | Registration start date | 2019-02-20T02:23:00                                             |
      | Registration end date   | 2019-02-20T14:23:00                                             |
      | Registration URL        | http://example.com                                              |
      | Entrance fee            | Free of charge                                                  |
      | Registration capacity   | 100 seats                                                       |
      | Online type             | facebook                                                        |
      | Online time start       | 2019-02-21T02:21:00                                             |
      | Online time end         | 2019-02-21T14:21:00                                             |
      | Online description      | Online description text                                         |
      | Online link             | uri: http://ec.europa.eu/info - title: Online link              |
      | Languages               | http://publications.europa.eu/resource/authority/language/0D0   |
      | Status                  | as_planned                                                      |
      | Organiser is internal   | 0                                                               |
      | Organiser name          | Name of the organiser                                           |
      | Event website           | uri: http://ec.europa.eu/info - title: Event website            |
      | Social media links      | uri: http://example.com - title: Twitter - link_type: twitter   |
      | Featured media          | Euro with miniature figurines                                   |
      | Featured media legend   | Media legend text                                               |
      | Venue                   | Name of the venue                                               |
      | Contact                 | Name of the general contact, Name of the press contact          |
    And I am an anonymous user
    When I am visiting the "Event demo page" content

    # Header elements.
    Then I should see "Event demo page"
    And I should see the text "Exhibitions" in the "page header meta" region

    # Icons with text.
    # @todo: Fix permission for anonymous to view skos entities.
    # And I should see the text "EU financing"
    And I should see the text "21 February 2019, 03:21 to 21 February 2019, 15:21"
    # @todo: Address not visible for anonymous user.
    # And I should see the text "Rue belliard 28, 1000 Brussels, Belgium"
    And I should see the text "Live streaming available"

    And the registration button is not active
    # Future event registration button has the title below.
    And I should see "Registration will open in 2 days 13 hours. You can register from 20 February 2019, 03:23, until 20 February 2019, 15:23."

    # Practical information.
    And I should see "Practical information"
    # @todo: Address not visible for anonymous user.
    # And I should see the text "Where"
    # And I should see the text "Rue belliard 28, 1000 Brussels, Belgium"
    And I should see the text "When"
    And I should see the text "Thursday 21 February 2019, 03:21 to Thursday 21 February 2019, 15:21"
    And I should see the text "Live stream"
    And I should see the text "Facebook"
    And I should see the text "Online link"
    And I should see the link "Online link"
    And I should see the text "Online time"
    And I should see the text "21 February 2019, 03:21 CET to 21 February 2019, 15:21 CET"
    # @todo: Skos entities are not visible for anonymous users.
    # And I should see the text "Languages"
    # And I should see the text "Valencian"
    And I should see the text "Organiser"
    And I should see the text "Name of the organiser"
    And I should see the text "Website"
    And I should see the link "Event website"
    And I should see the text "Number of seats"
    And I should see the text "100 seats"
    And I should see the text "Entrance fee"
    And I should see the text "Free of charge"

    # Social media links.
    And I should see the text "Social media" in the "Social media follow" region
    And I should see the link "Twitter" in the "Social media follow" region

    # Report texts should be visible when the event dates are in the past.
    # see @Scenario: When an anonymous user visits and event page the registration button and page text should change according to the actual time.
    And I should see "Description summary text"
    And I should see "Event description"
    But I should not see "Report summary text"
    And I should not see "Report text paragraph"

    # @todo implement a proper media assertion.
    And the "media container" element should contain "placeholder.png"
    And I should see the text "Media legend text"

    # Event contact values.
    And I should see the text "Contacts"
    And I should see the text "General contact"
    And I should see the text "Phone number"
    And I should see the text "+32477792933"
    And I should see the text "Address"
    And I should see the text "Budapest, General contact 1, 1011, Hungary"
    And I should see the text "general@example.com"
    And I should see the text "Press contact"
    And I should see the text "Name"
    And I should see the text "Name of the press contact"
    And I should see the text "+32477777777"
    And I should see the text "Szeged, Press contact 1, 6700, Hungary"
    And I should see the text "press@example.com"
