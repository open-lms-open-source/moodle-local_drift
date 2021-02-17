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
 * Testcase for local_drift plugin privacy implementation.
 *
 * @package    local_drift
 * @author     Guillermo Alvarez
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use core_privacy\local\request\transform;
use core_privacy\local\metadata\collection;
use local_drift\privacy\provider;

/**
 * Testcase for local Drift privacy implementation.
 *
 * @package    local_drift
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_drift_privacy_provider_testcase extends provider_testcase {

    public function setUp(): void {
        $this->resetAfterTest();
    }

    public function test_get_metadata() {
        $items = new collection('local_drift');
        $result = provider::get_metadata($items);
        $this->assertSame($items, $result);
        $this->assertInstanceOf(collection::class, $result);
    }

    private function mock_drift_user_subscription(int $userid) {
        global $DB;
        $data = new stdClass();
        $data->userid = $userid;
        $data->subscribed = 1;
        $DB->insert_record('local_drift_subscription', $data);
    }

    public function test_get_contexts_for_userid() {

        $user = $this->getDataGenerator()->create_user();
        $this->assertEmpty(provider::get_contexts_for_userid($user->id));

        $this->mock_drift_user_subscription($user->id);
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(1, $contextlist);

        $usercontext = \context_user::instance($user->id);
        $this->assertEquals($usercontext->id, $contextlist->get_contextids()[0]);

    }

    public function test_export_user_data() {

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->mock_drift_user_subscription($user1->id);

        $usercontext1 = \context_user::instance($user1->id);
        $usercontext2 = \context_user::instance($user2->id);

        $writer = writer::with_context($usercontext1);
        $this->assertFalse($writer->has_any_data());

        $approvedlist = new approved_contextlist($user1, 'local_drift', [$usercontext1->id]);
        provider::export_user_data($approvedlist);

        $this->assertTrue($writer->has_any_data());
        $data = $writer->get_data(['local_drift_subscription']);
        $this->assertEquals($user1->id, $data->userid);
        $this->assertEquals(transform::yesno(1), $data->subscribed);

        $writer = writer::with_context($usercontext2);

        $approvedlist = new approved_contextlist($user2, 'local_drift', [$usercontext2->id]);
        provider::export_user_data($approvedlist);
        $this->assertFalse($writer->has_any_data());

        $this->assertEmpty($writer->get_data(['local_drift_subscription']));
    }

    public function test_delete_data_for_all_users_in_context() {

        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->assertEquals(0, $DB->count_records('local_drift_subscription'));

        $this->mock_drift_user_subscription($user1->id);
        $this->mock_drift_user_subscription($user2->id);
        $this->assertEquals(2, $DB->count_records('local_drift_subscription'));

        $context = \context_user::instance($user1->id);
        provider::delete_data_for_all_users_in_context($context);
        $this->assertEquals(0, $DB->count_records('local_drift_subscription', ['userid' => $user1->id]));
        $this->assertEquals(1, $DB->count_records('local_drift_subscription'));
    }

    public function test_delete_data_for_user() {

        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->assertEquals(0, $DB->count_records('local_drift_subscription', ['userid' => $user->id]));

        $this->mock_drift_user_subscription($user->id);
        $this->assertEquals(1, $DB->count_records('local_drift_subscription', ['userid' => $user->id]));

        $usercontext = \context_user::instance($user->id);

        $approvedlist = new approved_contextlist($user, 'local_drift', [$usercontext->id]);
        provider::delete_data_for_user($approvedlist);

        $this->assertEquals(0, $DB->count_records('local_drift_subscription', ['userid' => $user->id]));
    }

    public function test_get_users_in_context() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->assertEquals(0, $DB->count_records('local_drift_subscription'));

        $this->mock_drift_user_subscription($user1->id);
        $this->assertEquals(1, $DB->count_records('local_drift_subscription'));

        $context1 = \context_user::instance($user1->id);
        $userlist = new \core_privacy\local\request\userlist($context1, 'local_drift');

        provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
        $expected = [$user1->id];
        $actual = $userlist->get_userids();
        $this->assertEquals($expected, $actual);

        $context2 = \context_user::instance($user2->id);
        $userlist = new \core_privacy\local\request\userlist($context2, 'local_drift');
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);
    }

    public function test_delete_data_for_users() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->assertEquals(0, $DB->count_records('local_drift_subscription'));

        $this->mock_drift_user_subscription($user1->id);
        $this->mock_drift_user_subscription($user2->id);
        $this->assertEquals(2, $DB->count_records('local_drift_subscription'));

        $context = \context_user::instance($user1->id);
        $userlist = new \core_privacy\local\request\approved_userlist($context, 'local_drift', [$user1->id, $user2->id]);
        provider::delete_data_for_users($userlist);

        $this->assertEquals(0, $DB->count_records('local_drift_subscription', ['userid' => $user1->id]));
        $this->assertEquals(1, $DB->count_records('local_drift_subscription'));
    }

}