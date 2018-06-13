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