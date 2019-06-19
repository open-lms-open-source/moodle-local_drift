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
 * Privacy implementation for the Drift Integration plugin.
 *
 * @package    local_drift
 * @author     Guillermo Alvarez
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_drift\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        // Get user context.
        $sql = "SELECT cx.id
                  FROM {context} cx
                  JOIN {local_drift_subscription} drift ON drift.userid = cx.instanceid
                 WHERE cx.instanceid = :userid and cx.contextlevel = :usercontext
              GROUP BY cx.id";

        $params = [
            'userid' => $userid,
            'usercontext' => CONTEXT_USER
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $context = \context_user::instance($userid);

        if (!in_array($context->id, $contextlist->get_contextids())) {
            return;
        }

        // Get user subscription.
        $record = $DB->get_record('local_drift_subscription', ['userid' => $userid]);
        if (!empty($record)) {
            $data = (object)[
                'userid' => transform::user($record->userid),
                'subscribed' => transform::yesno($record->subscribed)
            ];
            writer::with_context($context)->export_data(['local_drift_subscription'], $data);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (empty($context)) {
            return;
        }

        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }

        $DB->delete_records('local_drift_subscription', ['userid' => $context->instanceid]);

    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $context = \context_user::instance($userid);
        if (!in_array($context->id, $contextlist->get_contextids())) {
            return;
        }

        $DB->delete_records('local_drift_subscription', ['userid' => $userid]);
    }

    /**
     * Returns meta data about this system.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('local_drift_subscription',
            [
                'userid' => 'privacy:metadata:local_drift_subscription:userid',
                'subscribed' => 'privacy:metadata:local_drift_subscription:subscribed'
            ],
            'privacy:metadata:local_drift_subscription');

        $collection->add_external_location_link('drift', [
            'userid' => 'privacy:metadata:drift:userid',
            'name' => 'privacy:metadata:drift:name',
            'email' => 'privacy:metadata:drift:email',
            'country' => 'privacy:metadata:drift:country',
            'roleid' => 'privacy:metadata:drift:roleid',
            'rolename' => 'privacy:metadata:drift:rolename',
            'language' => 'privacy:metadata:drift:language',
            'issiteadmin' => 'privacy:metadata:drift:isisteadmin',
            'sitename' => 'privacy:metadata:drift:sitename',
        ], 'privacy:metadata:drift');

        return $collection;

    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!($context instanceof \context_user)) {
            return;
        }

        // Drift Subscriptions.
        $sql = "SELECT userid
                  FROM {local_drift_subscription} drift
                 WHERE drift.userid = :userid";

        $params = ['userid' => $context->instanceid];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();

        if (!($context instanceof \context_user)) {
            return;
        }

        $DB->delete_records('local_drift_subscription', ['userid' => $context->instanceid]);
    }
}
