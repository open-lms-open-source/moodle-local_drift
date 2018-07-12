<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test drift lib functions.
 *
 * @package   local_drift
 * @copyright Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/drift/lib.php');

/**
 * Class local_drift_testcase
 * @copyright Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_drift_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest(true);
    }

    public function test_roles_validation() {
        // Site Admins are allowed by default to subscribe them to Drift.
        $this->setAdminUser();
        $this->assertEquals(LOCAL_DRIFT_VALID_ACCESS, local_drift_validate_user_roles());
        $this->setUser(null);

        // Get all the roles.
        $allroles = get_all_roles();
        $role = reset($allroles);

        // Create a non site admin user an checks Drift valid roles.
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign($role->id, $user->id);
        $this->setUser($user);
        $this->assertEquals(LOCAL_DRIFT_INVALID_ACCESS, local_drift_validate_user_roles());
        $this->setUser(null);

        set_config('roles', $role->shortname, 'local_drift');
        $this->setUser($user);
        $this->assertEquals(LOCAL_DRIFT_VALID_ACCESS, local_drift_validate_user_roles());
    }

    public function test_detect_drift_roles_changes() {
        // Creates a student and a teacher to check several roles validation.
        $teacher = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'teacher');
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($teacher);

        // Checks if teacher role is a valid role by default.
        $this->assertFalse(local_drift_change_valid_roles());
        $this->assertEquals(LOCAL_DRIFT_INVALID_ACCESS, local_drift_validate_user_roles());

        // Change Drift valid roles in the same session and check that now teacher is a valid role..
        set_config('roles', 'teacher', 'local_drift');
        $this->assertTrue(local_drift_change_valid_roles());
        $this->assertEquals(LOCAL_DRIFT_VALID_ACCESS, local_drift_validate_user_roles());

        // Check that student is not a valid role.
        $this->setUser(null);
        $this->setUser($student);
        $this->assertFalse(local_drift_change_valid_roles());
        $this->assertEquals(LOCAL_DRIFT_INVALID_ACCESS, local_drift_validate_user_roles());
        $this->setUser(null);
    }

    public function test_drift_subscription() {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertEquals(0, local_drift_is_user_subscribed());

        // Simulate a subscription to Drift from this user.
        $record = new stdClass();
        $record->userid = $user->id;
        $record->subscribed = 1;
        $DB->insert_record('local_drift_subscription', $record);
        $cached = cache::make('local_drift', 'driftallowed');
        $cached->set('issubscribed', 1);
        $this->assertEquals(1, local_drift_is_user_subscribed());

        // Simulate an unsubscrition to Drift.
        $record->subscribed = 0;
        $DB->insert_record('local_drift_subscription', $record);
        $cached = cache::make('local_drift', 'driftallowed');
        $cached->set('issubscribed', 0);
        $this->assertEquals(0, local_drift_is_user_subscribed());
    }

}