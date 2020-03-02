@api @event
Feature: Event content type.
  As a user
  I want to access the content of a event
  So I can find the information I'm looking for.

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

    And I fill in "Description summary" with "Description summary text"
    And I fill in "Subject" with "EU financing"
    And I set "25-02-2019 10:30" as the "Start date" of "Event date"
    And I set "28-02-2019 14:30" as the "End date" of "Event date"

    # Registration field group.
    When I press "Registration"
    Then I fill in "Registration URL" with "http://example.com"
    And I set "23-02-2019 08:30" as the "Start date" of "Registration date"
    And I set "23-02-2019 18:30" as the "End date" of "Registration date"
    And I fill in "Entrance fee" with "Free of charge"
    And I fill in "Registration capacity" with "100 seats"

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
    And I set "26-02-2019 10:30" as the "Start date" of "Online time"
    And I set "26-02-2019 12:30" as the "End date" of "Online time"
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
    And I should see the text "25 February 2019, 10:30 to 28 February 2019, 14:30"
    And I should see the text "Rue belliard 28, 1000 Brussels, Belgium"
    And I should see the text "Live streaming available"

    # Practical information.
    And I should see "Practical information"
    And I should see the text "When"
    And I should see the text "Monday 25 February 2019, 10:30"
    And I should see the text "Where"
    And I should see the text "Rue belliard 28, 1000 Brussels, Belgium"
    And I should see the text "Live stream"
    And I should see the text "Online link"
    And I should see the link "Online link"
    And I should see the text "26 February 2019, 10:30 CET"
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
