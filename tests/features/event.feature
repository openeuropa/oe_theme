@api @event @datetime_testing
Feature: Event content type.
  As a user
  I want to access the content of a event
  So I can find the information I'm looking for.

  Scenario: When an anonymous user visits an event page the registration button and page text should change according to the current time.
    Given the following Event Content entity:
      | Title                   | My first event            |
      | Type                    | exhibitions               |
      | Description summary     | Event description summary |
      | Description             | Event description         |
      | Registration start date | 2020-03-01T12:30:00       |
      | Registration end date   | 2020-03-10T18:30:00       |
      | Start date              | 2020-06-15T12:30:00       |
      | End date                | 2020-06-20T18:30:00       |
      | Registration URL        | http://example.com        |
      | Summary for report      | Report summary            |
      | Report text             | Report text               |

    # Assert event rendering message days before registration starts.
    Given the date is "17 February 2020 2pm"
    When I am visiting the "My first event" content
    Then I should see the heading "Description"
    And I should see the text "Event description summary"
    And I should see the text "Event description"
    But I should not see the heading "Report"
    And I should not see the text "Report summary"
    And I should not see the text "Report text"
    And the registration button is not active
    And I should see "Registration will open in 1 week 5 days. You can register from 1 March 2020, 13:30, until 10 March 2020, 19:30." in the "event registration" region

    # Assert event rendering half an hour before the registration starts.
    When the date is "01 March 2020 12pm"
    And I run cron
    And I reload the page
    Then I should see the heading "Description"
    And I should see the text "Event description summary"
    And I should see the text "Event description"
    But I should not see the heading "Report"
    And I should not see the text "Report summary"
    And I should not see the text "Report text"
    And the registration button is not active
    And I should see "Registration will open in 1 hour 30 minutes. You can register from 1 March 2020, 13:30, until 10 March 2020, 19:30." in the "event registration" region

    # Assert event rendering while the registration is ongoing.
    When the date is "05 March 2020 2pm"
    And I run cron
    And I reload the page
    Then I should see the heading "Description"
    And I should see the text "Event description summary"
    And I should see the text "Event description"
    But I should not see the heading "Report"
    And I should not see the text "Report summary"
    And I should not see the text "Report text"
    And the registration button is active
    And I should see "Book your seat, 5 days left to register, registration will end on 10 March 2020, 19:30" in the "event registration" region

    # Assert event rendering after the registration has ended.
    When the date is "29 May 2020 2am"
    And I run cron
    And I reload the page
    Then I should see the heading "Description"
    And I should see the text "Event description summary"
    And I should see the text "Event description"
    But I should not see the heading "Report"
    And I should not see the text "Report summary"
    And I should not see the text "Report text"
    And the registration button should not be there
    But I should see "Registration period ended on Tuesday 10 March 2020, 19:30" in the "event registration" region

    # Assert event rendering after the event has ended.
    When the date is "21 June 2020 2pm"
    And I run cron
    And I reload the page
    Then I should see the heading "Report"
    And I should see the text "Report summary"
    And I should see the text "Report text"
    But I should not see the heading "Description"
    And I should not see the text "Event description summary"
    And I should not see the text "Event description"
    And I should not see the registration block

  @preserve_anonymous_permissions
  Scenario: As an anonymous user, when I visit an event I can see the information in the correct layout
    And I am on the homepage
    Given anonymous users can see events
    And the date is "17 February 2019 2pm"
    And the following images:
      | name                          | file            |
      | Euro with miniature figurines | placeholder.png |
    And the following Event Content entity:
      | Title                   | Event demo page                                               |
      | Type                    | exhibitions                                                   |
      | Introduction            | Event introduction text                                       |
      | Description summary     | Description summary text                                      |
      | Description             | Event description                                             |
      | Start date              | 2019-02-21T02:21:00                                           |
      | End date                | 2019-02-21T14:21:00                                           |
      | Status                  | as_planned                                                    |
      | Languages               | http://publications.europa.eu/resource/authority/language/0D0 |
    And I am an anonymous user
    When I am visiting the "Event demo page" content

    # Assert page header.
    Then I should see "Event demo page" in the "page header title"
    And I should see "Exhibitions" in the "page header meta"
    And I should see "Event introduction text" in the "page header intro"

    # Assert event details.
    And I should see the text "Description summary text" in the "event details"
    And I should see the text "Financing" in the "event details"
    And I should see the text "21 February 2019, 03:21 to 21 February 2019, 15:21" in the "event details"

    # Assert practical information.
    And I should see the heading "Practical information" in the "event practical information"
    And I should see "When" in the "event practical information"
    And I should see "Thursday 21 February 2019, 03:21 to Thursday 21 February 2019, 15:21" in the "event practical information"
    And I should see "Languages" in the "event practical information"
    And I should see "Valencian" in the "event practical information"

    # Assert absence of registration block.
    And I should not see the registration block

    # Add registration details.
    When the Event Content "Event demo page" is updated as follows:
      | Registration start date | 2019-02-20T02:23:00 |
      | Registration end date   | 2019-02-20T14:23:00 |
      | Registration URL        | http://example.com  |
    And I reload the page
    Then I should see the registration block
    And I should see "Registration will open in 2 days 13 hours. You can register from 20 February 2019, 03:23, until 20 February 2019, 15:23." in the "event registration"
    But the registration button is not active

    # Add related entities, such as venues and contacts and reload the page.
    Given the following Default Venue entity:
      | Name     | DIGIT                                                                                      |
      | Address  | country_code: BE - locality: Brussels - address_line1: Rue Belliard 28 - postal_code: 1000 |
      | Capacity | 12 people                                                                                  |
      | Room     | B-28 03/A150                                                                               |
    And the following Press Contact entity:
      | Name         | First press contact                                                                      |
      | Address      | country_code: HU - locality: Szeged - address_line1: Press contact 1 - postal_code: 6700 |
      | Email        | press1@example.com                                                                       |
      | Phone number | +32477777777                                                                             |
    And the following Press Contact entity:
      | Name         | Second press contact                                                                     |
      | Address      | country_code: HU - locality: Szeged - address_line1: Press contact 1 - postal_code: 6700 |
      | Email        | press2@example.com                                                                       |
      | Phone number | +32477777778                                                                             |
    And the following General Contact entity:
      | Name         | A general contact                                                                        |
      | Address      | country_code: HU - locality: Budapest - address_line1: General contact 1 - postal_code: 1011 |
      | Email        | general@example.com                                                                          |
      | Phone number | +32477792933                                                                                 |
    And the Event Content "Event demo page" is updated as follows:
      | Venue   | DIGIT                                                        |
      | Contact | First press contact, Second press contact, A general contact |
    And I reload the page

    # Assert event details.
    Then I should see "Brussels, Belgium" in the "event details"

    # Assert practical information.
    And I should see "Practical information" in the "event practical information"
    And I should see "Where" in the "event practical information"
    And I should see "DIGIT Rue Belliard 28, 1000 Brussels, Belgium" in the "event practical information"

    # Assert contacts.
    And I should see the heading "Contacts" in the "event contacts"
    And I should see the heading "General contact" in the "event contacts"
    And I should see "Phone number"
    And I should see "+32477792933"
    And I should see "Address"
    And I should see "Budapest, General contact 1, 1011, Hungary"
    And I should see "general@example.com"

    And I should see the heading "Press contact" in the "event contacts"
    And I should see "Name"
    And I should see "First press contact"
    And I should see "+32477777777"
    And I should see "Szeged, Press contact 1, 6700, Hungary"
    And I should see "press1@example.com"

    And I should see "Name"
    And I should see "Second press contact"
    And I should see "+32477777778"
    And I should see "Szeged, Press contact 1, 6700, Hungary"
    And I should see "press2@example.com"

    # Assert remaining event elements.
    Given the following image:
      | name              | file            |
      | Image placeholder | placeholder.png |
    And the Event Content "Event demo page" is updated as follows:
      | Entrance fee            | Free of charge                                                |
      | Registration capacity   | 12 seats                                                      |
      | Online type             | facebook                                                      |
      | Online time start       | 2019-02-21T02:21:00                                           |
      | Online time end         | 2019-02-21T14:21:00                                           |
      | Online description      | Online description text                                       |
      | Online link             | uri: http://ec.europa.eu/info - title: The online link title  |
      | Organiser is internal   | No                                                            |
      | Organiser name          | Name of the organiser                                         |
      | Event website           | uri: http://ec.europa.eu/info - title: Event website          |
      | Social media links      | uri: http://example.com - title: Twitter - link_type: twitter |
      | Featured media          | Image placeholder                                             |
      | Featured media legend   | Media legend text                                             |
    And I reload the page

    # Assert remaining practical information data.
    And I should see "Entrance fee" in the "event practical information"
    And I should see "Free of charge" in the "event practical information"

    And I should see "Number of seats" in the "event practical information"
    And I should see "12 seats" in the "event practical information"

    And I should see "Online link" in the "event practical information"
    And I should see "The online link title" in the "event practical information"

    And I should see "Live stream" in the "event practical information"
    And I should see "Facebook" in the "event practical information"

    And I should see "Online time" in the "event practical information"
    And I should see "21 February 2019, 03:21 CET to 21 February 2019, 15:21 CET" in the "event practical information"

    And I should see "Languages" in the "event practical information"
    And I should see "Valencian" in the "event practical information"

    And I should see "Organiser" in the "event practical information"
    And I should see "Name of the organiser" in the "event practical information"

    And I should see "Website" in the "event practical information"
    And I should see "Event website" in the "event practical information"

    # Assert social media links.
    And I should see "Social media" in the "event practical information"
    And I should see the link "Twitter" in the "event practical information"

    # Assert featured image.
    # @todo implement a proper media assertion.
    And the "media container" element should contain "placeholder"
    And I should see the text "Media legend text"

    # Assert changing organiser type.
    When the Event Content "Event demo page" is updated as follows:
      | Organiser is internal | Yes                                 |
      | Internal organiser    | Directorate-General for Informatics |
    And I reload the page
    Then I should not see "Name of the organiser" in the "event practical information"
    But I should see "Directorate-General for Informatics" in the "event practical information"

    # Assert showing report related information after the event ended.
    Given the date is "24 March 2025 2pm"
    And I run cron
    And I reload the page

    # If the event is over bu no report information is available, nothing changes.
    Then I should see the heading "Description"
    And I should see the text "Description summary text"
    And I should see the text "Event description"
    But I should not see the heading "Report"
    And I should not see the text "Report summary"
    And I should not see the text "Report text"

    # As soon as report information is available we show it instead of the ordinary event information.
    When the Event Content "Event demo page" is updated as follows:
      | Summary for report      | Report summary |
      | Report text             | Report text    |
    And I reload the page

    Then I should see the heading "Report"
    And I should see the text "Report summary"
    And I should see the text "Report text"
    But I should not see the heading "Description"
    And I should not see the text "Description summary text"
    And I should not see the text "Event description"
