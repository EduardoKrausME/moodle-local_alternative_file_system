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
 * Scheduled task to move files from tool_objectfs (DigitalOcean Spaces) to local_alternative_file_system destination.
 *
 * While Moodle is using tool_objectfs as $CFG->alternative_file_system_class, this task copies objects to the
 * destination configured in local_alternative_file_system (s3/space/gcs). When finished, you can switch
 * $CFG->alternative_file_system_class to \local_alternative_file_system\external_file_system.
 *
 * @package    local_alternative_file_system
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_alternative_file_system\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;
use core\output\notification;
use Exception;
use local_alternative_file_system\external_file_system;
use local_alternative_file_system\storages\s3\S3;
use RuntimeException;

/**
 *
 */
class move_from_objectfs extends scheduled_task {

    /**
     * @return string
     */
    public function get_name(): string {
        return get_string('task_movefromobjectfs', 'local_alternative_file_system');
    }

    /**
     * Execute task.
     *
     * @return void
     */
    public function execute(): void {
        global $PAGE, $DB, $CFG;

        $destconfig = get_config("local_alternative_file_system");

        $execute = optional_param("execute", 0, PARAM_INT);

        // ObjectFS (DigitalOcean Spaces) config.
        $dokey = get_config("tool_objectfs", "do_key");
        $dosecret = get_config("tool_objectfs", "do_secret");
        $doregion = get_config("tool_objectfs", "do_region");

        // This one is required in practice (Space name = bucket name).
        $dospace = get_config("tool_objectfs", "do_space");

        // Optional prefix (ObjectFS has "Key Prefix" for S3; for DO it may or may not exist).
        // We try a couple of common names and normalize it.
        $doprefix = get_config("tool_objectfs", "do_prefix");
        if ($doprefix === "") {
            $doprefix = get_config("tool_objectfs", "s3_keyprefix");
        }
        $doprefix = trim($doprefix, "/");
        if ($doprefix !== "") {
            $doprefix .= "/";
        }

        $errors = [];

        // --- Execute ---
        session_write_close();
        set_time_limit(0);
        @ob_end_flush();

        if (empty($destconfig->settings_destino) || $destconfig->settings_destino === "local") {
            $error = $PAGE->get_renderer("core")->render(
                new notification("Destino inválido. Configure o local_alternative_file_system para um destino remoto (s3/space/gcs).", notification::NOTIFY_ERROR)
            );
            mtrace($error);
            return;
        }

        if ($dokey === "" || $dosecret === "" || $doregion === "" || $dospace === "") {
            $error= $PAGE->get_renderer("core")->render(
                new notification(
                    "Config incompleta no tool_objectfs (necessário: do_key, do_secret, do_region e do_space).",
                    notification::NOTIFY_ERROR
                )
            );
            mtrace($error);
            return;
        }

        require_once(__DIR__ . "/classes/storages/s3/S3.php");
        require_once(__DIR__ . "/classes/storages/s3/S3Request.php");

        /**
         * Build the remote object key in object storage from a contenthash.
         *
         * @param string $contenthash
         * @param string $prefix
         * @return string
         */
        $buildobjectkey = static function(string $contenthash, string $prefix): string {
            $a1 = substr($contenthash, 0, 2);
            $a2 = substr($contenthash, 2, 2);
            return $prefix . "{$a1}/{$a2}/{$contenthash}";
        };

        if ($DB->get_dbfamily() == "postgres") {
            $rand = "RANDOM()";
        } else {
            $rand = "RAND()";
        }

        $sql = "
                SELECT o.id,
                       o.contenthash,
                       o.filesize,
                       o.filename,
                       o.mimetype
                  FROM {files} o
                 WHERE o.contenthash NOT IN (
                        SELECT laf.contenthash
                          FROM {local_alternativefilesystemf} laf
                   )
              ORDER BY {$rand}";
        $recordset = $DB->get_recordset_sql($sql, [], 0, 100000);

        $processed = 0;
        $tempdir = make_temp_directory("local_alternative_file_system/objectfs");

        foreach ($recordset as $obj) {
            $processed++;

            if ($DB->get_record("local_alternativefilesystemf", ["contenthash" => $obj->contenthash])) {
                continue;
            }

            mtrace("## File: {$obj->contenthash}");

            // Prefer local filedir if still exists, otherwise fetch from DO Spaces.
            $a1 = substr($obj->contenthash, 0, 2);
            $a2 = substr($obj->contenthash, 2, 2);
            $localfile = "{$CFG->dataroot}/filedir/{$a1}/{$a2}/{$obj->contenthash}";

            $sourcefile = $localfile;
            $downloaded = false;

            if (!file_exists($localfile)) {
                $objectkey = $buildobjectkey($obj->contenthash, $doprefix);
                $sourcefile = "{$tempdir}/{$obj->contenthash}";

                // Ensure old tmp is not reused.
                if (file_exists($sourcefile)) {
                    @unlink($sourcefile);
                }

                try {
                    $lifetime = time() + 4800;
                    $doendpoint = "{$doregion}.digitaloceanspaces.com";
                    S3::setConfig($dokey, $dosecret, $doendpoint);
                    $link = S3::getAuthenticatedURL($dospace, $objectkey, $lifetime);
                    $fp = fopen($sourcefile, 'wb');
                    if ($fp === false) {
                        throw new RuntimeException("Unable to open for writing: $sourcefile");
                    }
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
                        @unlink($sourcefile);
                        throw new RuntimeException("Erro cURL ($curlerrno): $curlerrmsg");
                    }

                    if ($httpcode < 200 || $httpcode >= 300) {
                        @unlink($sourcefile);
                        throw new RuntimeException("Download failed. HTTP $httpcode");
                    }

                    $downloaded = true;
                } catch (Exception $e) {
                    mtrace("{$obj->id} => {$obj->contenthash} => {$e->getMessage()}");
                    mtrace($PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR)));
                    continue;
                }
            }

            $mimetype = $obj->mimetype ?? "application/octet-stream";
            $filename = $obj->filename ?? "";
            $contentdisposition = $filename !== "" ? "inline; filename={$filename}" : "attachment";

            // Destination object key (path) for THIS plugin.
            try {
                $externalfilesystem = new external_file_system();
                $destkey = $externalfilesystem->get_local_path_from_hash($obj->contenthash);
            } catch (Exception $e) {
                mtrace( $PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR)));
                continue;
            }

            try {
                $externalfilesystem->upload($sourcefile, $destkey, $mimetype, $contentdisposition);
                mtrace("{$obj->id} => {$obj->contenthash} => OK");
            } catch (Exception $e) {
                mtrace("{$obj->id} => {$obj->contenthash} => Upload error");
                mtrace($PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR)));
            } finally {
                if ($downloaded && file_exists($sourcefile)) {
                    @unlink($sourcefile);
                }
            }

            @flush();
        }
        $recordset->close();
    }
}
