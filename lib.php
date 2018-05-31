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
 * @author     Guillermo Alvarez <guillermo.alvarez@blackboard.com>
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('LOCAL_DRIFT_VALID_ACCESS', 1);
define('LOCAL_DRIFT_INVALID_ACCESS', 2);

/**
 * Hook to load drift in every view.
 */
function local_drift_before_footer() {
    $canseecontent = local_drift_validate_user_roles();
    if ($canseecontent === LOCAL_DRIFT_VALID_ACCESS) {
        if (local_drift_is_user_subscribed()) {
            global $PAGE;
            $clientkey = get_config('local_drift', 'clientkey');
            $PAGE->requires->js_call_amd('local_drift/drift', 'init', array($clientkey));
        }
    }
}

/**
 * Validates if the user has a role in any context that allows to receive messages form drift.
 * @return int 1 if the user has a vlaid role, 2 otherwise.
 */
function local_drift_validate_user_roles() {
    $cached = cache::make('local_drift', 'driftallowed');
    $canseecontent = $cached->get('hasvalidroles');
    if (!$canseecontent) {
        global $DB, $USER;
        $validroles = explode(',', get_config('local_drift', 'roles'));
        // We need to store in cache if the user actually could use this plugin.
        $sql = "SELECT DISTINCT(r.shortname), r.id
                  FROM {role} r
                  JOIN {role_assignments} ra ON r.id = ra.roleid
                 WHERE ra.userid = :userid";
        $records = $DB->get_records_sql($sql, array('userid' => $USER->id));
        $userroles = array_keys($records);
        $hasavalidrole = false;
        $roles = [];
        foreach ($validroles as $role) {
            $hasavalidrole = $hasavalidrole || in_array($role, $userroles);
            if (in_array($role, $userroles)) {
                array_push($roles, $records[$role]->id);
            }
        }
        $canseecontent = ($hasavalidrole || is_siteadmin()) ? LOCAL_DRIFT_VALID_ACCESS : LOCAL_DRIFT_INVALID_ACCESS;
        $cached->set('hasvalidroles', $canseecontent);
        $cached->set('validuserroles', $roles);
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
