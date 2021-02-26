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
 * Drift integration version file
 *
 * @package    local_drift
 * @author     Guillermo Alvarez
 * @copyright  Copyright (c) 2018 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('LOCAL_DRIFT_VALID_ACCESS', 1);
define('LOCAL_DRIFT_INVALID_ACCESS', 2);
require_once($CFG->dirroot . '/admin/tool/mrooms/classes/local/user_data_aggregator.php');
require_once($CFG->dirroot . '/admin/tool/mrooms/classes/local/filestorage_table.php');

/**
 * Hook to load drift in every view.
 */
function local_drift_before_footer() {
    $canseecontent = local_drift_validate_user_roles();
    if ($canseecontent === LOCAL_DRIFT_VALID_ACCESS) {
        if (local_drift_is_user_subscribed()) {
            global $PAGE;
            $clientkey = get_config('local_drift', 'clientkey');
            if (empty($clientkey)) {
                return;
            }
            // Identify the user in Drift for this particular session.
            $PAGE->requires->js_call_amd('local_drift/drift', 'sendData', array($clientkey,
                local_drift_get_identification_data()));
        }
    }
}

/**
 * Checks if the roles that can subscribe to Drift are the same or have changed
 * @return bool True if drift valid roles have changed, false otherwise.
 */
function local_drift_change_valid_roles() {
    $cached = cache::make('local_drift', 'driftallowed');
    $validroles = $cached->get('validroles');
    $change = false;
    if (!empty($validroles)) {
        $driftroles = explode(',', get_config('local_drift', 'roles'));

        if (count($driftroles) === count($validroles)) {
            foreach ($driftroles as $key => $role) {
                $change = $change || !in_array($role, $validroles);
            }
        } else {
            $change = true;
        }
    }
    return $change;
}

/**
 * Validates if the user has a role in any context that allows to receive messages form drift.
 * @return int 1 if the user has a valid role, 2 otherwise.
 */
function local_drift_validate_user_roles() {
    $cached = cache::make('local_drift', 'driftallowed');
    $canseecontent = $cached->get('hasvalidroles');
    if (!$canseecontent || local_drift_change_valid_roles()) {
        global $DB, $USER;
        $validroles = explode(',', get_config('local_drift', 'roles'));
        // We need to store in cache if the user actually could use this plugin.
        $sql = "SELECT DISTINCT(r.shortname), r.id
                  FROM {role} r
                  JOIN {role_assignments} ra ON r.id = ra.roleid
                 WHERE ra.userid = :userid
              ORDER BY r.id";
        $records = $DB->get_records_sql($sql, array('userid' => $USER->id));
        $userroles = array_keys($records);
        $hasavalidrole = false;
        $roles = [];
        foreach ($validroles as $role) {
            $hasavalidrole = $hasavalidrole || in_array($role, $userroles);
            if (in_array($role, $userroles)) {
                $roles[$records[$role]->id] = $role;
            }
        }
        $canseecontent = ($hasavalidrole || is_siteadmin()) ? LOCAL_DRIFT_VALID_ACCESS : LOCAL_DRIFT_INVALID_ACCESS;
        $cached->set('hasvalidroles', $canseecontent);
        $cached->set('validuserroles', $roles);
        $cached->set('validroles', $validroles);
    }
    return $canseecontent;
}

/**
 * Checks if the user is subscribed to Drift.
 * @return int 1 if the user is subscribed, 0 otherwise.
 */
function local_drift_is_user_subscribed() {
    $cached = cache::make('local_drift', 'driftallowed');
    $issubscribed = $cached->get('issubscribed');
    if ($issubscribed === false) {
        // Add to cache the information about subscription.
        global $DB, $USER;

        local_drift_subscribe_new_site_admin();
        $record = $DB->get_record('local_drift_subscription', array('userid' => $USER->id));
        if (empty($record)) {
            $issubscribed = 0;
        } else {
            $issubscribed = $record->subscribed;
        }
        $cached->set('issubscribed', $issubscribed);
    }
    return $issubscribed;
}

/**
 * Hook to display drift subscription link in the user preferences view.
 * @param \core_user\output\myprofile\tree $tree
 * @param stdClass $user
 * @param bool $iscurrentuser
 * @param strClass $course
 * @return bool
 */
function local_drift_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (isguestuser()) {
        return false;
    }

    $canseecontent = local_drift_validate_user_roles();
    if ($canseecontent === LOCAL_DRIFT_VALID_ACCESS) {
        // Get the URL to subscribe of drift.
        $subscriptionurl = new moodle_url('/local/drift/view.php', array('controller' => 'subscription',
            'action' => 'view'));
        $string = get_string('drift_subscription', 'local_drift');
        $node = new core_user\output\myprofile\node('miscellaneous', 'driftsubscription', $string, null, $subscriptionurl);
        $tree->add_node($node);
    }
    return $canseecontent === LOCAL_DRIFT_VALID_ACCESS;
}

/**
 * Retrieves the user information that Drift requires to identify the user.
 * @return array
 */
function local_drift_get_identification_data() {
    global $USER, $CFG, $DB;

    $params = array();
    $cached = cache::make('local_drift', 'driftallowed');
    $roles = $cached->get('validuserroles');

    $avgusers = $cached->get('lastmonthusers');
    $avgusers = json_decode($avgusers);
    $avgregisterdusers = $cached->get('lastmonthresgitered');
    $avgregisterdusers = json_decode($avgregisterdusers);
    $lastmonth = date("M", strtotime("first day of previous month"));
    $report = new \tool_mrooms\local\user_data_aggregator();

    if (!empty($avgusers) && !empty($avgusers->{$lastmonth}) && !empty($avgregisterdusers) &&
        !empty($avgregisterdusers->{$lastmonth})) {
        $avgusers = $avgusers->{$lastmonth};
        $avgregisterdusers = $avgregisterdusers->{$lastmonth};
    } else {
        $data = $report->calculate_last_month_avg_users();
        $avgusers = $data->activeusers;
        $avgregisterdusers = $data->registeredusers;
        $cached->set('lastmonthusers', json_encode([$lastmonth => $avgusers]));
        $cached->set('lastmonthresgitered', json_encode([$lastmonth => $avgregisterdusers]));
    }

    $storagedata = $DB->get_records_sql(\tool_mrooms\local\filestorage_table::QUERYSTORAGE, []);
    $storageoverage = false;
    if (!empty($storagedata) && !empty($storagedata['mdata_filedir_storage'])
        && !empty($storagedata['s3_filedir_storage']) && !empty($CFG->tool_mrooms_licensed_storage)) {
        $storageoverage = $storagedata['mdata_filedir_storage']->value + $storagedata['s3_filedir_storage']->value
            > $CFG->tool_mrooms_licensed_storage ? true : false;
    }

    $roleid = !empty($roles) ? min(array_keys($roles)) : null;
    $params['userid'] = $USER->id . '-' . $CFG->wwwroot;
    $params['data'] = [];
    $params['data']['email'] = $USER->email;
    $params['data']['name'] = format_string(fullname($USER));
    $params['data']['issiteadmin'] = (is_siteadmin()) ? 'true' : 'false';
    $params['data']['country'] = $USER->country;
    $params['data']['roleid'] = (is_siteadmin()) ? 'site admin' : $roleid;
    $params['data']['rolename'] = (is_siteadmin()) ? 'site admin' : $roles[$roleid];
    $params['data']['sitename'] = $CFG->wwwroot;
    $params['data']['language'] = current_language();
    $params['data']['avgactiveusers'] = $avgusers;
    $params['data']['avgregisteredusers'] = $avgregisterdusers;
    $params['data']['purchasedusers'] = !empty($CFG->tool_mrooms_licensed_users) ? $CFG->tool_mrooms_licensed_users : false;
    $params['data']['purchasedstorage'] = !empty($CFG->tool_mrooms_licensed_storage) ?
        format_float(($CFG->tool_mrooms_licensed_storage / 1024) / 1024, 2) . ' GB' : false;
    $params['data']['storageoverage'] = $storageoverage;
    $params['data']['useroverage'] = !empty($CFG->tool_mrooms_licensed_users) ?
        $avgusers > $CFG->tool_mrooms_licensed_users : false;

    return $params;
}

/**
 * Subscribes by default a site admin to Drift.
 */
function local_drift_subscribe_new_site_admin() {
    global $USER, $DB;

    // Checks if user is an admin and he has a subscription to Drift.
    if (is_siteadmin() && !$DB->record_exists('local_drift_subscription', array('userid' => $USER->id))) {
        // Creates a subscription to Drift for a new site admin.
        $record = new stdClass();
        $record->userid = $USER->id;
        $record->subscribed = 1;
        $DB->insert_record('local_drift_subscription', $record);
    }
}