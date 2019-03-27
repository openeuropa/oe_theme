@api
Feature: Site branding
  In order to have my site branded with the official European Commission visual identity
  As a product owner
  I want to make sure that all necessary site branding features are provided by the OpenEuropa Theme.

  Scenario Outline: The European Commission logo is available throughout the site
    When I am on the homepage
    Then I should <see homepage> the "<element>" element in the "<region>"
    When I am on "the user registration page"
    Then I should <see other> the "<element>" element in the "<region>"

    Examples:
      | element     | region | see homepage | see other |
      | logo        | header | see          | see       |
      | breadcrumb  | page   | not see      | see       |
      | page header | page   | not see      | see       |
