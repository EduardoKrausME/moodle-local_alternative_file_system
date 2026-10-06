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
 * settings.js
 *
 * @package   local_alternative_file_system
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core_form/changechecker"], function ($, changeChecker) {
    return {
        init: function () {
            let $form = $("#adminsettings");
            let $select = $("#id_s_local_alternative_file_system_storage_destination");

            if (!$form.length || !$select.length) {
                return;
            }

            $select.on("change", function () {
                let formNode = $form.get(0);

                // Prevent the browser "Leave site? Changes may not be saved" warning.
                try {
                    changeChecker.markFormSubmitted(formNode);
                } catch (e) {
                    // Ignore if not available for any reason.
                }

                // Use native submit (not jQuery) to ensure proper submit flow.
                formNode.submit();
            });
        },
    };
});
