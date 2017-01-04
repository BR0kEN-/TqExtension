@api @debug
Feature: Tq Context
  Scenario: Test "assertElementAttribute" method
    Given I am logged in with credentials:
      | username  | admin |
      | password  | admin |
    Then I am on the "/" page and HTTP code is "200"
    When I work with elements in "body" region
    Then should see the "div" element with "id" attribute having "page-wrapper" value
