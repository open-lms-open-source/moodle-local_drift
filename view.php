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

// @codingStandardsIgnoreStart
// We need to prevent code sniffer looking here in order to prevent latest check
// that insists on login check after inclusion of config.php - which we do somewhere else.
require_once('../../config.php');

global $CFG;

require($CFG->dirroot . '/local/mr/bootstrap.php');

mr_controller::render('local/drift', 'pluginname', 'local_drift');
