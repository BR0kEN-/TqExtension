@api
Feature: Installation
  As a Drupal developer
  I want to ensure behat is setup and configured
  So that I can start writing tests for my project.

  Scenario: Verify The site has a login page with user and pass fields.
    Given I am unauthorized user
	When I go to "/"
	Then I should see no errors
