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
             WHERE filename LIKE '__%'
               AND filesize > 2
               AND mimetype IS NOT NULL";
    $files = $DB->get_recordset_sql($sql);
    /** @var object $file */
    foreach ($files as $file) {
        $a1 = substr($file->contenthash, 0, 2);
        $a2 = substr($file->contenthash, 2, 2);
        $localfile = "{$CFG->dataroot}/filedir/{$a1}/{$a2}/{$file->contenthash}";

        if (file_exists($localfile)) {
            echo "{$file->id} => {$file->filename} => {$localfile} - OK<br>";
        } else {
            echo "{$file->id} => {$file->filename} - Baixar<br>";

            try {
                $url = $externalfilesystem->get_remote_path_from_hash($file->contenthash);
                $filecontent = file_get_contents($url);

                mkdir("{$CFG->dataroot}/filedir/{$a1}");
                mkdir("{$CFG->dataroot}/filedir/{$a1}/{$a2}");

                file_put_contents($localfile, $filecontent);

            } catch (Exception $e) {
                echo $PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR));
            }
        }
    }
    $files->close();
} else {
    $decsep = get_string("decsep", "langconfig");
    $thousandssep = get_string("thousandssep", "langconfig");
    echo get_string("migrate_link", "local_alternative_file_system");
}

echo $OUTPUT->footer();
