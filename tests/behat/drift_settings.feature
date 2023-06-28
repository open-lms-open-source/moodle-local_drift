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
# @copyright  Copyright (c) 2018 Open LMS (https://www.openlms.net)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_drift
Feature: Test settings form in Drift plugin
  As an admin
  I should be able of store a client key and configure the plugin.

  @javascript
  Scenario: Test connection button behaviour in drift plugin with and without client key.
    Given I log in as "admin"
    And I navigate to "Plugins > Drift Integration Plugin" in site administration
    And I wait until the page is ready
    And I should see "Save form to test connection"
    And I should see "Client Secret Key"
    And I click on "//a[@data-passwordunmask='edit']" "xpath_element"
    Then I set the field with xpath "//input[@id='id_s_local_drift_clientkey']" to "dummy"
    # Please uncomment when INT-14017 is reverted.
    # And I should see "Roles Enabled"
    # And I set the field "Roles Enabled" to "teacher"
    And I press "Save changes"
    And I wait until the page is ready
    Then I should see "Test connection"
    # Please uncomment when INT-14017 is reverted.
    # And the field "Roles Enabled" matches value "teacher"
    And I should see "Client Secret Key"
    And I click on "//a[@data-passwordunmask='edit']" "xpath_element"
    Then I set the field with xpath "//input[@id='id_s_local_drift_clientkey']" to ""
    And I press "Save changes"
    And I wait until the page is ready
    And I should see "Save form to test connection"

  @javascript
  Scenario: Test modal with an invalid client key.
    Given I log in as "admin"
    And I navigate to "Plugins > Drift Integration Plugin" in site administration
    And I wait until the page is ready
    And I click on "//a[@data-passwordunmask='edit']" "xpath_element"
    Then I set the field with xpath "//input[@id='id_s_local_drift_clientkey']" to "anything"
    And I press "Save changes"
    And I wait until the page is ready
    And I press "Test connection"
    And I wait "3" seconds
    Then I should see "Connection not verified. Check client key."

  @javascript
  Scenario: Test modal with a valid client key.
    Given I log in as "admin"
    And I navigate to "Plugins > Drift Integration Plugin" in site administration
    And I wait until the page is ready
    And I click on "//a[@data-passwordunmask='edit']" "xpath_element"
    Then I set the field with xpath "//input[@id='id_s_local_drift_clientkey']" to "right-password"
    And I press "Save changes"
    And I wait until the page is ready
    And I press "Test connection"
    And I wait "3" seconds
    Then I should see "Connection verified."
