@api @project
Feature: Project content type.
  As a user
  I want to access the content of a project
  So I can find the information I'm looking for.

  Scenario: Project period field has label "Start date" if start date is equal with end date
    Given I am an anonymous user
    And the following Project Content entity:
      | Title                     | Project page |
      | Body text                 | Body text    |
      | Project period start date | 2010-07-01   |
      | Project period end date   | 2010-07-01   |
    When I visit the "Project page" content
    Then I should see the text "Start date"
    And I should see the text "01.07.2010"

    # Change Project period end date.
    When the Project Content "Project page" is updated as follows:
      | Project period start date | 2010-07-01 |
      | Project period end date   | 2012-01-01 |
    And I reload the page
    Then I should see the text "Project duration"
    And I should see the text "01.07.2010 - 01.01.2012"

