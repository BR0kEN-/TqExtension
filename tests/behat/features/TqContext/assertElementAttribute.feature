@api @debug
Feature: Tq Context
  Scenario: Test "assertElementAttribute" method
    Given I am on the "/" page and HTTP code is "200"
    Then I work with elements in "body" region
    And should see the "div" element with "id" attribute having "page-wrapper" value
