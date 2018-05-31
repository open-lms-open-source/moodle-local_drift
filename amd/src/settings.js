/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   local_drift
 * @author    Guillermo Leon Alvarez Salamanca <guillermo.alvarez@blackboard.com>
 * @copyright Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * JS code to test Drift connection in local_drift settings file.
 */

define(['jquery', 'core/str', 'core/modal_factory', 'core/templates', 'local_drift/drift'],
    function($, str, ModalFactory, Templates, drift) {
        
        return {
            
            init : function (clientKey) {

                // Create button to test drift connection.
                var div = $('#admin-clientkey');
                var button = $('<button>Test Button</button>').attr('id', 'drift_test_connection');
                button.attr('class', 'btn btn-primary').attr('type', 'submit');

                // Disable the button.
                var disableButton = function(text) {
                    $(button).attr('disabled', 'disabled');
                    $(button).text(text)
                };

                // First bring the required stings for the button.
                var strings = str.get_strings([
                    {key: 'drift_testconnection', component: 'local_drift'},
                    {key: 'drift_buttondisabled', component: 'local_drift'}
                ]);

                // Load button with the right string.
                $.when(strings).done(function (localizedStrings) {
                    // Selector of the client key input.
                    var input = $('#id_s_local_drift_clientkey');

                    // Check if user has stored the client key or not.
                    if (input.val() == '') {
                        disableButton(localizedStrings[1]);
                    } else {
                        button.text(localizedStrings[0]);
                    }

                    // Disabled button when a change occurs on the client key.
                    $(input).on('input', function () {
                        disableButton(localizedStrings[1]);
                    });

                });

                // Strings for Modal.
                var modalStrings = str.get_strings([
                    {key: 'drift_testconnection', component: 'local_drift'},
                    {key: 'drift_connection_verified', component: 'local_drift'},
                    {key: 'drift_connection_fail', component: 'local_drift'}
                ]);

                var localizedModalStrings = [];

                // Add the modal to DOM when the strings are ready.
                $.when(modalStrings).done(function (localizedStrings) {
                    localizedModalStrings = localizedStrings;
                    ModalFactory.create({
                        title: localizedStrings[0],
                        body: Templates.render('local_drift/settings', {}),
                        type: ModalFactory.types.DEFAULT
                    }, button).done(function(modal){
                        // Append button in the DOM.
                        div.append(button);
                    });
                });

                button.click(function (e) {
                    e.preventDefault();
                    setDefault();
                    $('#drift_result').text('...');
                    var s = drift.getScript(clientKey);
                    $.get(s.src).done(function() {
                        swapClasses(true);
                        $('#drift_result').text(localizedModalStrings[1]);
                        drift.testConnection(clientKey);
                    }).fail(function () {
                        swapClasses(false);
                        $('#drift_result').text(localizedModalStrings[2]);
                    });
                });

                /**
                 * Changes the class of the html element to match the response status.
                 * @param {bool} status Response status
                 */
                var swapClasses = function(status) {
                    var selector = '#drift_testing';
                    if (status) {
                        if ($(selector).hasClass('alert-warning')) {
                            $(selector).removeClass("alert-warning").addClass('alert-success');
                        } else if ($(selector).hasClass('alert-danger')) {
                            $(selector).removeClass("alert-danger").addClass('alert-success');
                        }
                    } else {
                        if ($(selector).hasClass('alert-warning')) {
                            $(selector).removeClass("alert-warning").addClass('alert-danger');
                        } else if ($(selector).hasClass('alert-success')) {
                            $(selector).removeClass("alert-success").addClass('alert-danger');
                        }
                    }
                };

                /**
                 * Set the default state for the modal
                 */
                var setDefault = function() {
                    var selector = '#drift_testing';
                    if ($(selector).hasClass('alert-success')) {
                        $(selector).removeClass("alert-success").addClass('alert-warning');
                    } else if ($(selector).hasClass('alert-danger')) {
                        $(selector).removeClass("alert-danger").addClass('alert-warning');
                    }
                };
            }

        }
});