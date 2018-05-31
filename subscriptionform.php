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

require_once("$CFG->libdir/formslib.php");

class subscription_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;

        global $DB, $USER;
        $record = $DB->get_record('local_drift_subscription', ['userid' => $USER->id]);
        $default = empty($record) ? 0 : $record->subscribed;

        $mform->addElement('advcheckbox', 'drift_usersubscription', get_string('drift_subscription', 'local_drift'),
            get_string('drift_usersubscription', 'local_drift'), null, array(0, 1));
        $mform->addHelpButton('drift_usersubscription', 'drift_usersubscription', 'local_drift');
        $mform->setDefault('drift_usersubscription', $default);

        $this->add_action_buttons(false);
    }

}