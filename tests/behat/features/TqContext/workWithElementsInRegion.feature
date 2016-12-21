@api
Feature: Tq Context
  Scenario: Test "workWithElementsInRegion" method
    Given I am on the "/" page
    And work with elements in "head" region
    Then I should see the "meta" element with "http-equiv" attribute having "Content-Type" value
    Then I checkout to whole page
    And work with elements in "#header" region
    Then I should see the "#logo" element with "rel" attribute having "home" value
