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

namespace local_alternative_file_system\storages\gcs;

use dml_exception;
use Exception;
use Google\Cloud\Storage\StorageClient;
use local_alternative_file_system\i_file_system;
use local_alternative_file_system\storages\storage_file_system;
use stored_file;

defined('MOODLE_INTERNAL') || die;
require_once(__DIR__ . "/vendor/autoload.php");

/**
 * gcs_file_system file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gcs_file_system extends storage_file_system implements i_file_system {

    /**
     * get_instance function.
     *
     * @return StorageClient
     * @throws Exception
     */
    private function get_instance() {
        static $storage = null;
        if ($storage) {
            return $storage;
        }

        $config = get_config("local_alternative_file_system");

        $storage = new StorageClient([
            "keyFile" => json_decode($config->settings_gcs_keyfile, true),
        ]);

        return $storage;
    }

    /**
     * Test config function.
     *
     * @throws Exception
     */
    public function test_config() {
        global $CFG;

        $config = get_config("local_alternative_file_system");

        $settingspath = preg_replace('/[^a-zA-Z0-9\.\-]/', "", $config->settings_path);
        if ($settingspath != $config->settings_path) {
            set_config("settings_path", $settingspath, "local_alternative_file_system");
        }

        $pathname = "{$CFG->tempdir}/teste.txt";
        file_put_contents($pathname, "123");

        $bucket = S3::bucket($config->settings_gcs_bucketname);
        $filename = $this->get_local_path_from_hash(md5("1"));
        $stream = fopen($pathname, "r");
        $options = ["name" => $filename];
        $bucket->upload($stream, $options);

        $object = $bucket->object($filename);
        $object->delete();
    }

    /**
     * Get the full path for the specified hash, including the path to the filedir.
     *
     * This is typically either the same as the local filepath, or it is a streamable resource.
     *
     * See https://secure.php.net/manual/en/wrappers.php for further information on valid wrappers.
     *
     * @param string $contenthash
     * @param bool $fetchifnotfound
     * @return string The full path to the content file
     * @throws Exception
     */
    public function get_remote_path_from_hash($contenthash, $fetchifnotfound = false) {

        $config = get_config("local_alternative_file_system");

        $bucket = S3::bucket($config->settings_gcs_bucketname);
        $object = $bucket->object($this->get_local_path_from_hash($contenthash));

        return $object->signedUrl(time() + 1500);
    }

    /**
     * Copy stored_file content to a local path.
     *
     * @param stored_file $file
     * @param string $target
     * @return bool
     * @throws Exception
     */
    public function copy_content_from_storedfile(stored_file $file, $target) {
        $config = get_config('local_alternative_file_system');

        $bucket = S3::bucket($config->settings_gcs_bucketname);
        $object = $bucket->object($this->get_local_path_from_hash($file->get_contenthash()));

        $object->downloadToFile($target);

        $this->report_save($file->get_contenthash());
        return true;
    }

    /**
     * Remove a file from the remote storage if it is no longer referenced in {files}.
     *
     * @param string $contenthash
     * @return bool
     * @throws Exception
     */
    public function remove_file($contenthash) {
        global $DB;

        // If any file record still references this contenthash, do not delete remotely.
        if ($DB->record_exists('files', ['contenthash' => $contenthash])) {
            return true;
        }

        // No references in mdl_files: safe to delete from remote storage.
        $config = get_config('local_alternative_file_system');
        $uri = $this->get_local_path_from_hash($contenthash);

        try {
            $bucket = S3::bucket($config->settings_gcs_bucketname);
            $bucket->object($uri)->delete();
        } catch (\Throwable $e) {
        }

        // Remove tracking row only for this storage.
        $DB->delete_records('local_alternativefilesystemf', [
            'contenthash' => $contenthash,
            'storage' => $config->settings_destino,
        ]);

        return true;
    }

    /**
     * upload function.
     *
     * @param string $sourcefile
     * @param string $filename
     * @param string $contenttype
     * @param string $contentdisposition
     * @throws Exception
     */
    public function upload($sourcefile, $filename, $contenttype, $contentdisposition) {
        $config = get_config("local_alternative_file_system");

        $bucket = S3::bucket($config->settings_gcs_bucketname);
        $stream = fopen($sourcefile, "r");
        $options = [
            "name" => $filename,
            "metadata" => [
                "contentType" => $contenttype,
                "contentDisposition" => $contentdisposition,
            ],
        ];
        $bucket->upload($stream, $options);

        $contenthash = pathinfo($filename, PATHINFO_FILENAME);
        $this->report_save($contenthash);
    }
}
