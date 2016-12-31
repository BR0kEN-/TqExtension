@api
Feature: Tq Context
  Scenario: Test "assertElementAttribute" method
    Given I am on the "/" page
    Then I work with elements in "body" region
    And should see the "div" element with "id" attribute having "page-wrapper" value
