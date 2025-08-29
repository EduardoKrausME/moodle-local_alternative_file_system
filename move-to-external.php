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
 * move-to-external file.
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use local_alternative_file_system\external_file_system;

define('OPEN_INTERNAL', true);

require_once("../../config.php");

ignore_user_abort(true);
set_time_limit(0); // Executa indefinidamente.
ini_set('max_execution_time', 0); // Alternativa.

require_login();
require_capability("moodle/site:config", context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_pagetype("my-index");
$PAGE->set_url(new moodle_url("/local/alternative_file_system/move-to-external.php"));
$PAGE->set_title(get_string("migrate_title", "local_alternative_file_system"));
$PAGE->set_heading(get_string("migrate_title", "local_alternative_file_system"));

echo $OUTPUT->header();

$config = get_config("local_alternative_file_system");
$externalfilesystem = new external_file_system();

if (optional_param("execute", false, PARAM_INT)) {

    session_write_close();
    set_time_limit(0);
    ob_end_flush();

    $sql = "SELECT id, contenthash, mimetype, filename
              FROM {files}
             WHERE contenthash NOT IN (
                    SELECT contenthash
                      FROM {local_alternativefilesystemf}
                     WHERE storage = '{$config->settings_destino}'
                 )
               AND filename    LIKE '__%'
               AND filesize    > 2
               AND mimetype    IS NOT NULL";
    $files = $DB->get_records_sql($sql);
    /** @var object $file */
    foreach ($files as $file) {
        $remotefilename = $externalfilesystem->get_local_path_from_hash($file->contenthash);

        echo "{$file->id} => {$file->filename} => {$remotefilename}<br>";
        $a1 = substr($file->contenthash, 0, 2);
        $a2 = substr($file->contenthash, 2, 2);
        $sourcefile = "{$CFG->dataroot}/filedir/{$a1}/{$a2}/{$file->contenthash}";
        try {
            $externalfilesystem->upload($sourcefile, $remotefilename, $file->mimetype, "inline; filename={$file->filename}");
        } catch (Exception $e) {
            echo $PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR));
        }
    }
} else {

    $decsep = get_string("decsep", "langconfig");
    $thousandssep = get_string("thousandssep", "langconfig");
    $a = [
        "missing" => number_format($externalfilesystem->missing_count(), 0, $decsep, $thousandssep),
        "sending" => number_format($externalfilesystem->sending_count(), 0, $decsep, $thousandssep),
    ];
    echo get_string("migrate_total", "local_alternative_file_system", $a);
    echo get_string("migrate_link", "local_alternative_file_system");
}

echo $OUTPUT->footer();
