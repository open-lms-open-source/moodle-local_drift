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

require_once($CFG->dirroot.'/local/drift/subscriptionform.php');
require_once($CFG->dirroot.'/local/drift/lib.php');

/**
 * Class local_drift_controller_subscription
 * @package   local_drift
 * @copyright Copyright (c) 2018 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_drift_controller_subscription extends mr_controller {

    public function init() {
        global $PAGE, $USER;
        $PAGE->set_heading(format_string(fullname($USER)));
    }

    /**
     * Main view action
     */
    public function view_action() {

        if (isguestuser()) {
            throw new moodle_exception('invaliduser', 'error');

        }

        if (isloggedin()) {
            global $DB, $USER, $OUTPUT;

            $mform = new subscription_form($this->url);
            $fromform = $mform->get_data();

            if ($fromform) {
                // Modify the subscription for this user.
                $subscription = $fromform->drift_usersubscription;
                $record = $DB->get_record('local_drift_subscription', array('userid' => $USER->id));
                if (empty($record)) {
                    $record = new stdClass();
                    $record->userid = $USER->id;
                    $record->subscribed = $subscription;
                    $DB->insert_record('local_drift_subscription', $record);
                } else {
                    $record->subscribed = $subscription;
                    $DB->update_record('local_drift_subscription', $record);
                }

                // Store this new status in cache.
                $cached = cache::make('local_drift', 'driftallowed');
                $cached->set('issubscribed', $subscription);

                // Send user data to Drift.
                if ($record->subscribed) {
                    $params = local_drift_get_identification_data();
                    global $PAGE;

                    $clientkey = get_config('local_drift', 'clientkey');
                    $PAGE->requires->js_call_amd('local_drift/drift', 'sendData', array($clientkey, $params));
                }

            }
            // Display the form via controller.
            $this->print_header();
            echo $OUTPUT->notification(get_string('drift_usersubscription_policy', 'local_drift'), 'info');
            $mform->display();
            $this->print_footer();
        }
    }

}
