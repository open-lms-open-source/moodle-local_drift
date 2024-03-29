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
 * Language strings for local_drift plugin.
 *
 * @package   local_drift
 * @author    Guillermo Leon Alvarez Salamanca
 * @copyright Copyright (c) 2018 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Drift Integration Plugin';
$string['cachedef_driftallowed'] = 'This is used to cached if the user has a valid role to interact with Drift service.';
$string['drift'] = 'Drift';
$string['drift_buttondisabled'] = 'Save form to test connection';
$string['drift_clientkey'] = 'Client Secret Key';
$string['drift_clientkey_desc'] = 'Client secret key provided by Drift.';
$string['drift_credentials'] = 'Drift credentials: ';
$string['drift_connection_verified'] = 'Connection verified.';
$string['drift_connection_fail'] = 'Connection not verified. Check client key.';
$string['drift_response_header'] = 'Drift server response:';
$string['drift_roles'] = 'Roles Enabled';
$string['drift_roles_desc'] = 'Choose the roles that should receive messages from Drift.';
$string['drift_subscription'] = 'Drift subscription';
$string['drift_testconnection'] = 'Test connection';
$string['drift_usersubscription'] = 'Subscribe to Drift notifications.';
$string['drift_usersubscription_help'] = 'Check this box to receive messages from Drift.';
$string['drift_usersubscription_policy'] = 'When you enable your Drift subscription, you agree to send your name, email, country, language, and role to the Drift platform.';
$string['drift_welcome_message'] = 'Drift connection is working.';
$string['privacy:metadata:local_drift_subscription'] = 'Information about the subscription to Drift service.';
$string['privacy:metadata:local_drift_subscription:userid'] = 'The ID of the current user.';
$string['privacy:metadata:local_drift_subscription:subscribed'] = 'The status of Drift subscription for the current user.';
$string['privacy:metadata:drift'] = 'Drift Platform';
$string['privacy:metadata:drift:country'] = 'The user\'s country information is sent from Moodle to Drift as part of user authentication process.';
$string['privacy:metadata:drift:email'] = 'The user\'s email is sent from Moodle to Drift as part of user authentication process.';
$string['privacy:metadata:drift:isisteadmin'] = 'Drift requires to know if user is a site admin or not.';
$string['privacy:metadata:drift:language'] = 'The user\'s language is sent from Moodle to Drift as part of user authentication process.';
$string['privacy:metadata:drift:name'] = 'The user\'s name is sent from Moodle to Drift as part of user authentication process.';
$string['privacy:metadata:drift:roleid'] = 'Drift requires the user roleid to filter users by role on its server.';
$string['privacy:metadata:drift:rolename'] = 'Drift requires the user rolename to identify users by role and sent aimed marketing messages.';
$string['privacy:metadata:drift:sitename'] = 'The site name is sent to Drift as part of user authentication process.';
$string['privacy:metadata:drift:userid'] = 'The user\'s id is sent from Moodle to Drift as part of user authentication process.';
