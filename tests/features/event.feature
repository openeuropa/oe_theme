@api
Feature: Event content type.
  As a user
  I want to access the content of a event
  So I can find the information I'm looking for.

  @javascript @cleanup:media @av_portal @run
  Scenario: I can create an Event page and I can see the information with the correct layout.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create av_portal_photo media, administer event venue entities, administer event profile entities, view the administration theme" permission

    # Create a "Media AV portal photo".
    And I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15"
    And I press "Save"
    And I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://audiovisual.ec.europa.eu/en/photo/P-039321~2F00-04"
    And I press "Save"

    # Start filling in all fields.
    And I am on "the event creation page"
    And I select "Info days" from "Type"
    And I fill in "Title" with "Event demo page"

    # Registration field group.
    When I press "Registration"
    Then I fill in "Registration URL" with "http://example.com"
    And I select "Open" from "Registration status"
    And I break
    And I fill in "Registration start date" with the date "02/23/2019"
    And I fill in "Registration start date" with the time "02:23:00AM"
    And I fill in "Registration end date" with the date "02/23/2019"
    And I fill in "Registration end date" with the time "02:23:00PM"
    And I fill in "Entrance fee" with "Free of charge"
    And I fill in "Registration capacity" with "100 seats"

    And I fill in "Description summary" with "Description summary text"
    And I fill in "Subject" with "EU financing"
    And I fill in "Start date" with the date "02/21/2019"
    And I fill in "Start date" with the time "02:21:00AM"
    And I fill in "End date" with the date "02/21/2019"
    And I fill in "End date" with the time "02:21:00PM"

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
    And I fill in "Online time start" with the date "02/22/2019"
    And I fill in "Online time start" with the time "02:22:00AM"
    And I fill in "Online time end" with the date "02/22/2019"
    And I fill in "Online time end" with the time "02:22:00PM"
    And I fill in "Online description" with "Online description text"
    And I fill in "URL" with "http://ec.europa.eu/2" in the "Online link" region
    And I fill in "Link text" with "Online link" in the "Online link" region

    And I select "As planned" from "Status"
    And I fill in "Languages" with "Hungarian"

    # Organiser field group.
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
    And I fill in "Use existing media" with "Visit by Federica Mogherini, Vice-President of the EC, and Johannes Hahn, Member of the EC, to Romania" in the "Event report" region

    # Event partner field group is not rendered yet.
    # Event contact field group.
    When I press "Add new contact"
    And I wait for AJAX to finish
    Then I fill in "Name" with "Name of the event contact" in the "Event contact" region
    And I select the radio button "General contact"
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
