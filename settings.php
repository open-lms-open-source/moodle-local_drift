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
 * Add page to admin menu.
 *
 * @package   local_drift
 * @author    Guillermo Leon Alvarez Salamanca <guillermo.alvarez@blackboard.com>
 * @copyright Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $PAGE;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_drift', get_string('pluginname', 'local_drift'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configpasswordunmask('local_drift/clientkey',
        get_string('drift_clientkey', 'local_drift'), get_string('drift_clientkey_desc', 'local_drift'), ''));

    $allroles = get_all_roles();
    $roles = [];
    foreach ($allroles as $key => $role) {
        $roles[$role->shortname] = $role->name;
    }

    $settings->add(new admin_setting_configmultiselect('local_drift/roles',
        get_string('drift_roles', 'local_drift'), get_string('drift_roles_desc', 'local_drift'), [], $roles));

    $clientkey = get_config('local_drift', 'clientkey');
    $PAGE->requires->js_call_amd('local_drift/settings', 'init', array($clientkey));

}