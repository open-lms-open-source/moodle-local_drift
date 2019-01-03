# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
#
# Tests for visibility of admin block by user type and page.
#
# @package    local_drift
# @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_drift
Feature: Test subscription in Drift plugin
  As a Teacher
  I should be able to subscribe to Drift.

  Background:
    Given the following config values are set as admin:
      |roles| teacher | local_drift |
    And the following "courses" exist:
      | fullname | shortname | format | category | groupmode | enablecompletion |
      | Course 1 | C1        | topics | 0        | 1         | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | admin2   | Admin     | 1        | admin2@example.com   |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C1     | teacher |
      | student1 | C1     | student |

  Scenario: Link for Drift subscription should appear only to roles enable on Drift settings and site admins.
    Given I log in as "student1"
      And I go to my profile page
      And I should not see "Drift subscription"
      And I log out
     Then I log in as "teacher1"
      And I go to my profile page
      And I should see "Drift subscription"
      And I log out
     Then I log in as "admin"
      And I go to my profile page
      And I should see "Drift subscription"
      And I log out

  Scenario: Subscription form should save state of user subscription.
    Given I log in as "teacher1"
      And I go to my profile page
      And I follow "Drift subscription"
     Then I wait until the page is ready
      And the field "Drift subscription" matches value "0"
      And I set the field "Drift subscription" to "1"
      And I press "Save changes"
     Then I log out
      And I log in as "teacher1"
      And I go to my profile page
      And I follow "Drift subscription"
      And the field "Drift subscription" matches value "1"

  @javascript
  Scenario: Site admin should be enrolled by default to Drift and he could change the state of his subscription.
    Given I log in as "admin"
      And I go to my profile page
      And I should see "Drift subscription"
      And I follow "Drift subscription"
      And the field "Drift subscription" matches value "1"
      And I set the field "Drift subscription" to "0"
      And I press "Save changes"
      And the field "Drift subscription" matches value "0"
     Then I log out
      And I log in as "admin"
      And I go to my profile page
      And I follow "Drift subscription"
     Then the field "Drift subscription" matches value "0"

  @javascript
  Scenario: New site admin should be enrolled by default to Drift.
    Given I log in as "admin2"
    And I go to my profile page
    And I should not see "Drift subscription"
    And I log out
   Then I log in as "admin"
    And I navigate to "Users" in site administration
    And I follow "Site administrators"
    And I click on "//*[@id='addselect']/optgroup/option[1]" "xpath_element"
    And I click on "Add" "button"
    And I wait until the page is ready
    And I click on "Continue" "button"
    And I log out
   Then I log in as "admin2"
    And I go to my profile page
    And I follow "Drift subscription"
    And the field "Drift subscription" matches value "1"