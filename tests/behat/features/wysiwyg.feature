Feature: Check that WysiywgTrait works.

  @api
  Scenario: Assert "When I fill in WYSIWYG "field" with "value"" works as expected
    Given page content:
      | title             |
      | [TEST] Page title |
    And I am logged in as a user with the "administrator" role
    And I edit "page" "[TEST] Page title"
    When I fill in WYSIWYG "Body" with "[TEST] body"
    When I fill in WYSIWYG "Description" with "[TEST] description"
    And save screenshot
    And I press "Save"
    Then I should see "[TEST] body"
    And I should see "[TEST] description"

  @api @javascript
  Scenario: Assert "When I fill in WYSIWYG "field" with "value"" works as expected with JS driver
    Given page content:
      | title                       |
      | [TEST-JS-Driver] Page title |
    And I am logged in as a user with the "administrator" role
    And I edit "page" "[TEST-JS-Driver] Page title"
    When I fill in WYSIWYG "Body" with "[TEST-JS-Driver] body"
    When I fill in WYSIWYG "Description" with "[TEST-JS-Driver] description"
    And save screenshot
    And I press "Save"
    Then I should see "[TEST-JS-Driver] body"
    And I should see "[TEST-JS-Driver] description"
