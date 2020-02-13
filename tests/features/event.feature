@api @event @datetime_testing
Feature: Event content type.
  As a user
  I want to access the content of a event
  So I can find the information I'm looking for.

  @javascript
  Scenario: When an anonymous user visits and event page the registration button and page text should change according to the actual time.
    Given I am on the homepage
    And the date is "17 February 2020 2pm"
    And the following Event Content:
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
    And I should see "Registration will open in 1 week 5 days. You can register from 1 March 2020, 13:30, until 10 March 2020, 19:30."
    And I should see the registration button "Register here" inactive

    When the date is "05 March 2020 2pm"
    And I run cron
    And I reload the page
    Then I should see "Book your seat, 5 days left to register, registration will end on 10 March 2020, 19:30"
    And I should see the registration button "Register here" active

    When the date is "21 June 2020 2pm"
    And I run cron
    And I reload the page
    Then I should see "Registration period ended on Tuesday 10 March 2020, 19:30"
    # If active, link element is used with button style.
    And I should see the registration button "Register here" inactive

    When the date is "15 March 2020 2pm"
    Then I should see the heading "Report"
    And I should not see the heading "Description"
    And I should see the text "Report summary"
    And I should see the text "Report text"
    And I should not see the text "Event description summary"
    And I should not see the text "Event description"

  @javascript
  Scenario: I can create an Event page and I can see the information with the correct layout.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create av_portal_photo media, manage corporate content entities, view the administration theme" permission

    And the following images:
      | name                          | file            |
      | Euro with miniature figurines | placeholder.png |

    # Start filling in all fields.
    And I am on "the event creation page"
    And I select "Info days" from "Type"
    And I fill in "Title" with "Event demo page"

    # Registration field group.
    When I press "Registration"
    Then I fill in "Registration URL" with "http://example.com"
    And I fill in "Start date" of "Registration date" with the date "02/23/2019"
    And I fill in "Start date" of "Registration date" with the time "02:23:00AM"
    And I fill in "End date" of "Registration date" with the date "02/23/2019"
    And I fill in "End date" of "Registration date" with the time "02:23:00PM"
    And I fill in "Entrance fee" with "Free of charge"
    And I fill in "Registration capacity" with "100 seats"

    And I fill in "Description summary" with "Description summary text"
    And I fill in "Subject" with "EU financing"
    And I fill in "Start date" of "Event date" with the date "02/21/2019"
    And I fill in "Start date" of "Event date" with the time "02:21:00AM"
    And I fill in "End date" of "Event date" with the date "02/21/2019"
    And I fill in "End date" of "Event date" with the time "02:21:00PM"

    # Venue reference by inline entity form.
    And I fill in "Name" with "Name of the venue"
    And I fill in "Capacity" with "Capacity of the venue"
    And I fill in "Room" with "Room of the venue"
    And I select "Belgium" from "Country"
    And I wait for AJAX to finish
    And I fill in "Street address" with "Rue belliard 28"
    And I fill in "Postal code" with "1000"
    And I fill in "City" with "Brussels"

    # Online field group.
    When I press "Online"
    Then I select "Facebook" from "Online type"
    And I fill in "Start date" of "Online time" with the date "02/22/2019"
    And I fill in "Start date" of "Online time" with the time "02:22:00AM"
    And I fill in "End date" of "Online time" with the date "02/22/2019"
    And I fill in "End date" of "Online time" with the time "02:22:00PM"
    And I fill in "Online description" with "Online description text"
    And I fill in "URL" with "http://ec.europa.eu/2" in the "Online link" region
    And I fill in "Link text" with "Online link" in the "Online link" region

    And I select "As planned" from "Status"
    And I fill in "Languages" with "Hungarian"

    # Organiser field group.
    When I uncheck "Organiser is internal"
    Then I fill in "Organiser name" with "Organiser name"

    # Event website field group.
    And I fill in "URL" with "http://ec.europa.eu" in the "Website" region
    And I fill in "Link text" with "Website" in the "Website" region

    # Add a social media link
    And I fill in "URL" with "http://twitter.com" in the "Social media links" region
    And I fill in "Link text" with "Twitter" in the "Social media links" region
    And I select "Twitter" from "Link type"

    # Description field group.
    And I fill in "Use existing media" with "Euro with miniature figurines" in the "Description" region
    And I fill in "Featured media legend" with "Euro with miniature figurines"
    And I fill in "Full text" with "Full text paragraph"

    # Report field group.
    When I press "Event report"
    And I fill in "Report text" with "Report text paragraph"
    And I fill in "Summary for report" with "Report summary text"

    # Event partner field group.
    When I press "Add new partner"
    And I wait for AJAX to finish
    Then I fill in "Name" with "Name of the event partner" in the "Event partner" region
    And I fill in "Use existing media" with "Euro with miniature figurines" in the "Event partner" region
    And I fill in "Website" with "http://eventpartner.com" in the "Event partner" region

    # Event contact field group.
    When I press "Add new contact"
    And I wait for AJAX to finish
    Then I fill in "Name" with "Name of the event contact" in the "Event contact" region
    And I select "Hungary" from "Country" in the "Event contact" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Back street 3" in the "Event contact" region
    And I fill in "Postal code" with "9000" in the "Event contact" region
    And I fill in "City" with "Budapest" in the "Event contact" region
    And I fill in "Email" with "test@example.com" in the "Event contact" region
    And I fill in "Phone number" with "0488779033" in the "Event contact" region

    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    When I press "Save"

    # Header elements.
    Then I should see "Event demo page"
    And I should see the text "Info days" in the "page header meta" region

    # Icons with text.
    And I should see the text "EU financing"
    And I should see the text "21 February 2019, 02:21"
    And I should see the text "Rue belliard 28, 1000 Brussels, Belgium"
    And I should see the text "Live streaming available"

    # Past event registration button has the title below.
    And I should see "Registration period ended on Saturday 23 February 2019, 14:23"

    # Practical information.
    And I should see "Practical information"
    And I should see the text "When"
    And I should see the text "Thursday 21 February 2019, 02:21"
    And I should see the text "Where"
    And I should see the text "Rue belliard 28, 1000 Brussels, Belgium"
    And I should see the text "Live stream"
    And I should see the text "Online link"
    And I should see the link "Online link"
    And I should see the text "22 February 2019, 02:22 CET"
    And I should see the text "Languages"
    And I should see the text "Hungarian"
    And I should see the text "Organiser"
    And I should see the text "Organiser name"
    And I should see the text "Website"
    And I should see the link "Website"
    And I should see the text "Number of seats"
    And I should see the text "100 seats"
    And I should see the text "Entrance fee"
    And I should see the text "Free of charge"

    # Social media.
    And I should see the text "Social media" in the "Social media follow" region
    And I should see the link "Twitter" in the "Social media follow" region

    # Report texts should be visible when the event dates are in the past.
    # @see Scenario: Description of the event is shown if event is in the future or ongoing.
    And I should not see "Description summary text"
    And I should not see "Full text paragraph"
    But I should see "Report summary text"
    And I should see "Report text paragraph"

    # @todo implement a proper media assertion.
    And the "media container" element should contain "placeholder.png"
    And I should see the text "Euro with miniature figurines"

    # Event contact values.
    And I should see the text "Contacts"
    And I should see the text "General contact"
    And I should see the text "General contact"
    And I should see the text "Budapest, Back street 3, 9000, Hungary"
    And I should see the text "test@example.com"
    And I should see the text "0488779033"

  @javascript
  Scenario: Description of the event is shown if event is in the future or ongoing.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create av_portal_photo media, manage corporate content entities, view the administration theme" permission

    And the following images:
      | name                          | file            |
      | Euro with miniature figurines | placeholder.png |

    # Start filling in the required fields fields.
    And I am on "the event creation page"
    And I select "Info days" from "Type"
    And I fill in "Title" with "Event demo page"
    And I fill in "Description summary" with "Description summary text"
    And I fill in "Subject" with "EU financing"
    And I fill in "Start date" of "Event date" with the date "02/21/2032"
    And I fill in "Start date" of "Event date" with the time "02:21:00AM"
    And I fill in "End date" of "Event date" with the date "02/21/2032"
    And I fill in "End date" of "Event date" with the time "02:21:00PM"
    And I select "As planned" from "Status"
    And I fill in "Languages" with "Hungarian"

    # Registration field group.
    When I press "Registration"
    Then I fill in "Registration URL" with "http://example.com"
    And I fill in "Start date" of "Registration date" with the date "02/23/2032"
    And I fill in "Start date" of "Registration date" with the time "02:23:00AM"
    And I fill in "End date" of "Registration date" with the date "02/23/2032"
    And I fill in "End date" of "Registration date" with the time "02:23:00PM"

    # Venue reference by inline entity form.
    And I fill in "Name" with "Name of the venue"

    # Organiser field group.
    When I uncheck "Organiser is internal"
    Then I fill in "Organiser name" with "Organiser name"

    # Description field group.
    And I fill in "Use existing media" with "Euro with miniature figurines" in the "Description" region
    And I fill in "Featured media legend" with "Euro with miniature figurines"
    And I fill in "Full text" with "Full text paragraph"

    # Report field group.
    When I press "Event report"
    And I fill in "Report text" with "Report text paragraph"
    And I fill in "Summary for report" with "Report summary text"

    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    When I press "Save"

    Then I should not see the text "Report"
    And I should not see the text "Report text paragraph"
    And I should not see the text "Report summary text"
    But I should see the text "Description"
    And I should see the text "Full text paragraph"
    And I should see the text "Description summary text"

  @javascript
  Scenario: Registration button block changes according to the dates of the registration.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create av_portal_photo media, manage corporate content entities, view the administration theme" permission

    And the following images:
      | name                          | file            |
      | Euro with miniature figurines | placeholder.png |

    # Start filling in the required fields fields.
    And I am on "the event creation page"
    And I select "Info days" from "Type"
    And I fill in "Title" with "Event demo page"
    And I fill in "Description summary" with "Description summary text"
    And I fill in "Subject" with "EU financing"
    And I fill in "Start date" of "Event date" with the date "02/21/2032"
    And I fill in "Start date" of "Event date" with the time "02:21:00AM"
    And I fill in "End date" of "Event date" with the date "02/21/2032"
    And I fill in "End date" of "Event date" with the time "02:21:00PM"
    And I select "As planned" from "Status"
    And I fill in "Languages" with "Hungarian"

    # Registration field group.
    When I press "Registration"
    Then I fill in "Registration URL" with "http://example.com"
    And I fill in "Start date" of "Registration date" with the date "02/23/2032"
    And I fill in "Start date" of "Registration date" with the time "02:23:00AM"
    And I fill in "End date" of "Registration date" with the date "02/23/2032"
    And I fill in "End date" of "Registration date" with the time "02:23:00PM"

    # Venue reference by inline entity form.
    And I fill in "Name" with "Name of the venue"

    # Organiser field group.
    When I uncheck "Organiser is internal"
    Then I fill in "Organiser name" with "Organiser name"

    # Description field group.
    And I fill in "Use existing media" with "Euro with miniature figurines" in the "Description" region
    And I fill in "Featured media legend" with "Euro with miniature figurines"
    And I fill in "Full text" with "Full text paragraph"

    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    When I press "Save"

    # The registration is in the future.
    # @todo Test this part too "Registration will open in @time_left."
    Then I should see "You can register from 23 February 2032, 02:23, until 23 February 2032, 14:23."

    # Change the registration status to active by moving the end date to the future.
    When I click "Edit"
    And I press "Registration"
    And I fill in "Start date" of "Registration date" with the date "02/23/2019"
    And I fill in "Start date" of "Registration date" with the time "02:23:00AM"
    And I fill in "End date" of "Registration date" with the date "02/25/2019"
    And I fill in "End date" of "Registration date" with the time "02:23:00PM"
    And I press "Save"

    Then I should not see the button "Registration will open on 23 February 2032, 02:23, until 25 February 2032, 14:23."
    # Button is active so it becomes a link. (ECL does not provide button as link)
    # @todo: Mock current time to test this.
    # But I should see the link "Book your seat, 1 day left to register."
