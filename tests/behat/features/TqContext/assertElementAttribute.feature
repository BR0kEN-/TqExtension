@api
Feature: Tq Context
  Scenario: Test "assertElementAttribute" method
    Given I am on the "/" page
    Then I work with elements in "html"
    And should see the "head" element with "profile" attribute having "http://www.w3.org/1999/xhtml/vocab" value
