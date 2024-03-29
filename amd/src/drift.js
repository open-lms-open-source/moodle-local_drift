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
 * @author    Guillermo Leon Alvarez Salamanca
 * @copyright Copyright (c) 2018 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * JS code to load the javascript Drift client.
 */
define(['jquery','core/str'], function($, str) {

    /* eslint-disable */
    // Start of Async Drift Code.
    "use strict";
    !function() {
        var t = window.driftt = window.drift = window.driftt || [];
        if (!t.init) {
            if (t.invoked) return void (window.console && console.error && console.error("Drift snippet included twice."));
            t.invoked = !0, t.methods = [ "identify", "config", "track", "reset", "debug", "show", "ping", "page", "hide", "off", "on" ],
                t.factory = function(e) {
                    return function() {
                        var n = Array.prototype.slice.call(arguments);
                        return n.unshift(e), t.push(n), t;
                    };
                }, t.methods.forEach(function(e) {
                t[e] = t.factory(e);
            }), t.load = function(t) {
                    var e = 3e5, n = Math.ceil(new Date() / e) * e, o = document.createElement("script");
                    o.type = "text/javascript", o.async = !0, o.crossorigin = "anonymous", o.src = "https://js.driftt.com/include/" + n + "/" + t + ".js";
                    var i = document.getElementsByTagName("script")[0];
                    i.parentNode.insertBefore(o, i);
            };
        }
    }();
    drift.SNIPPET_VERSION = '0.3.1';
    // End of Async Drift Code.

    /**
     * Load drift client.
     * @param {string} clientKey
     */
    var load = function (clientKey) {
        drift.load(clientKey);
    };

    var DRIFT_WIDGET_SELECTOR = '.drift-open-widget'

    $(function() {
        drift.on('ready', (api) => {
            $(DRIFT_WIDGET_SELECTOR).click((e) => {
                e.preventDefault();
                api.sidebar.open();
            });
        });
    });
    /* eslint-enable */
    return {

        /**
         * Get the url of the script.
         */
        /* eslint-disable */
        getScript: function(t) {
            var e = 3e5, n = Math.ceil(new Date() / e) * e, o = document.createElement("script");
            o.type = "text/javascript", o.async = !0, o.crossorigin = "anonymous", o.src = "https://js.driftt.com/embeds/" + n + "/" + t + ".json";
            return o;
        },

        /**
         * Initialize Drift.
         */
        init: function(clientKey) {
            load(clientKey);
        },

        /**
         * Test Drift connection.
         */
        testConnection: function(clientKey) {
            load(clientKey);
            var message = str.get_string('drift_welcome_message', 'local_drift');

            $.when(message).done(function(localizedMessage) {
                drift.on('ready',function(api) {
                    api.showWelcomeMessage({
                        message: localizedMessage
                    });
                });
            });
        },

        /**
         * Send user data to Drift.
         * @param {string} clientKey
         * @param {array} params
         */
        sendData: function (clientKey, params) {
            load(clientKey);
            drift.identify(params.userid, params.data);
        }
        /* eslint-enable */
    };
});
