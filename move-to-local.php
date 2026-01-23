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
use local_alternative_file_system\storages\s3\S3;
use local_alternative_file_system\storages\s3\s3_file_system;
use local_alternative_file_system\storages\storage_file_system;

define('OPEN_INTERNAL', true);

global $CFG, $DB, $OUTPUT, $PAGE;

require_once("../../config.php");

ignore_user_abort(true);
set_time_limit(0); // Executa indefinidamente.
ini_set('max_execution_time', 0);

require_login();
require_capability("moodle/site:config", context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_pagetype("my-index");
$PAGE->set_url(new moodle_url("/local/alternative_file_system/move-to-external.php"));
$PAGE->set_title(get_string("migrate_title_tolocal", "local_alternative_file_system"));
$PAGE->set_heading(get_string("migrate_title_tolocal", "local_alternative_file_system"));

echo $OUTPUT->header();

$config = get_config("local_alternative_file_system");
$externalfilesystem = new external_file_system();

if (optional_param("execute", false, PARAM_INT)) {
    session_write_close();
    set_time_limit(0);
    ob_end_flush();

    $s3filesystem = new s3_file_system();

    $sql = "SELECT id, contenthash, mimetype, filename
              FROM {files}
             WHERE filename <> '.'
               AND mimetype IS NOT NULL";
    $files = $DB->get_recordset_sql($sql);
    /** @var object $file */
    foreach ($files as $file) {
        $a1 = substr($file->contenthash, 0, 2);
        $a2 = substr($file->contenthash, 2, 2);
        $localfile = "{$CFG->dataroot}/filedir/{$a1}/{$a2}/{$file->contenthash}";

        if (file_exists($localfile) && filesize($localfile)) {
            echo "# {$file->filename} - Local OK<br>";
            continue;
        }

        echo "# {$file->filename} - To go down<br>";
        try {
            $url = $externalfilesystem->get_remote_path_from_hash($file->contenthash);

            $objectkey = $s3filesystem->get_local_path_from_hash($file->contenthash);
            $link = $s3filesystem->get_authenticated_url($objectkey, time() + 4800);
            @mkdir("{$CFG->dataroot}/filedir/{$a1}/{$a2}", 0777, true);
            $fp = fopen($localfile, 'wb');

            $ch = curl_init($link);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_FAILONERROR => false,
                CURLOPT_USERAGENT => 'cURL',
            ]);
            $ok = curl_exec($ch);

            $curlerrno = curl_errno($ch);
            $curlerrmsg = curl_error($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);
            fclose($fp);

            if ($ok === false || $curlerrno) {
                echo "Erro cURL ({$curlerrno}): {$curlerrmsg}<br>";
            }
            if ($httpcode < 200 || $httpcode >= 300) {
                echo "Download failed. HTTP {$httpcode}<br>";
            }
        } catch (Exception $e) {
            echo $PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR));
        }
    }
    $files->close();
} else {
    echo get_string("migrate_total_local", "local_alternative_file_system");
    echo get_string("migrate_link", "local_alternative_file_system");
}

echo $OUTPUT->footer();
