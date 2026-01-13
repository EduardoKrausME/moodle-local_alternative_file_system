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
 * move-from-objectfs file.
 *
 * Read tool_objectfs_objects (DigitalOcean Spaces) and copy objects to the
 * destination configured in local_alternative_file_system.
 *
 * @package    local_alternative_file_system
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_alternative_file_system\task\move_from_objectfs;

require_once(__DIR__ . "/../../config.php");

session_write_close();
ignore_user_abort(true);
set_time_limit(0);
ini_set('max_execution_time', 0);

require_login();
require_capability("moodle/site:config", context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_pagetype("my-index");
$PAGE->set_url(new moodle_url("/local/alternative_file_system/move-from-objectfs.php"));
$PAGE->set_title(get_string("migrate_title", "local_alternative_file_system"));
$PAGE->set_heading(get_string("migrate_title", "local_alternative_file_system"));

echo $OUTPUT->header();

$execute = optional_param("execute", 0, PARAM_INT);

if (!$execute) {
    echo html_writer::div(
        "This script copies objects from <strong>tool_objectfs (DigitalOcean Spaces)</strong>" .
        " to the destination <strong>local_alternative_file_system</strong>.", "mb-3"
    );

    echo html_writer::start_tag("ul");
    echo html_writer::tag("li", "Source (tool_objectfs): DigitalOcean Spaces");
    echo html_writer::tag("li", "Destination (local_alternative_file_system): " .
        s($destconfig->settings_destino ?? "-"));
    echo html_writer::end_tag("ul");

    $link = new moodle_url("/local/alternative_file_system/move-from-objectfs.php", ["execute" => 1]);
    echo html_writer::div(html_writer::link($link, "Start copy now"), "mt-3");
} else {
    echo '<pre>';
    (new move_from_objectfs())->execute();
    echo '</pre>';
}

$next = new moodle_url("/local/alternative_file_system/move-from-objectfs.php", ["execute" => 1]);
echo html_writer::div(html_writer::link($next, "Execute"), "mt-3");

echo $OUTPUT->footer();
