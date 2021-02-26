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
 * Drift integration version file.
 *
 * @package    local_drift
 * @author     Guillermo Alvarez
 * @copyright  Copyright (c) 2018 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Updates local_drift data model.
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_drift_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2018052904) {

        // Define field id to be added to local_drift_subscription.
        $table = new xmldb_table('local_drift_subscription');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('subscribed', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'userid');

        // Adding keys to table plagiarism_safeassign_instr.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $key = new xmldb_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Conditionally launch create table for plagiarism_safeassign_instr.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Drift savepoint reached.
        upgrade_plugin_savepoint(true, 2018052904, 'local', 'drift');
    }

    if ($oldversion < 2019020500) {

        set_config('roles', '', 'local_drift'); // We need to empty this config value.

        // Drift savepoint reached.
        upgrade_plugin_savepoint(true, 2019020500, 'local', 'drift');
    }

    return true;

}