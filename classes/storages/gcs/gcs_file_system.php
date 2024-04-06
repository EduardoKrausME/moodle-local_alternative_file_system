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
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_alternative_file_system\storages\gcs;

use dml_exception;
use Exception;
use Google\Cloud\Storage\StorageClient;
use local_alternative_file_system\i_file_system;
use local_alternative_file_system\storages\storage_file_system;
use stored_file;

defined('MOODLE_INTERNAL') || die;
require_once(__DIR__ . "/vendor/autoload.php");

class gcs_file_system extends storage_file_system implements i_file_system {

    /**
     * @return StorageClient
     *
     * @throws Exception
     */
    private function get_instance() {
        static $storage = null;
        if ($storage) {
            return $storage;
        }

        $config = get_config("local_alternative_file_system");

        $storage = new StorageClient([
            'keyFile' => json_decode($config->settings_gcs_keyfile, true)
        ]);

        return $storage;
    }

    /**
     * @throws dml_exception
     *
     * @throws Exception
     */
    public function test_config() {
        global $CFG;

        $config = get_config("local_alternative_file_system");

        $settingspath = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $config->settings_path);
        if ($settingspath != $config->settings_path) {
            set_config("settings_path", $settingspath, "local_alternative_file_system");
        }

        $pathname = "{$CFG->tempdir}/teste.txt";
        file_put_contents($pathname, "123");

        $bucket = $this->get_instance()->bucket($config->settings_gcs_bucketname);
        $filename = $this->get_local_path_from_hash(md5("1"));
        $stream = fopen($pathname, 'r');
        $options = ['name' => $filename];
        $bucket->upload($stream, $options);

        $object = $bucket->object($filename);
        $object->delete();
    }

    /**
     * @param string $contenthash The content hash
     * @param bool $fetchifnotfound
     *
     * @return string The full path to the content file
     *
     * @throws dml_exception
     * @throws Exception
     */
    public function get_remote_path_from_hash($contenthash, $fetchifnotfound = false) {

        $config = get_config("local_alternative_file_system");

        $bucket = $this->get_instance()->bucket($config->settings_gcs_bucketname);
        $object = $bucket->object($this->get_local_path_from_hash($contenthash));

        return $object->signedUrl(500);
    }

    /**
     * Copy content of file to given pathname.
     *
     * @param stored_file $file The file to be copied
     * @param string $target real path to the new file
     *
     * @return bool success
     *
     * @throws Exception
     */
    public function copy_content_from_storedfile(stored_file $file, $target) {
        $config = get_config("local_alternative_file_system");

        $bucket = $this->get_instance()->bucket($config->settings_gcs_bucketname);
        $object = $bucket->object($this->get_remote_path_from_storedfile($file));

        $object->copy($target);

        $this->report_save($file->get_contenthash(), "gcs");

        return true;
    }

    /**
     * Removes the file.
     *
     * @param string $contenthash
     *
     * @return bool
     *
     * @throws dml_exception
     * @throws Exception
     */
    public function remove_file($contenthash) {
        global $DB;

        $config = get_config("local_alternative_file_system");

        $bucket = $this->get_instance()->bucket($config->settings_gcs_bucketname);
        $object = $bucket->object($this->get_local_path_from_hash($contenthash));
        $object->delete();

        $DB->delete_records("alternative_file_system_file", ["contenthash" => $contenthash]);

        return true;
    }

    /**
     * @param string $sourcefile
     * @param string $filename
     * @param string $contenttype
     * @param string $contentdisposition
     *
     * @throws Exception
     */
    public function upload($sourcefile, $filename, $contenttype, $contentdisposition) {
        $config = get_config("local_alternative_file_system");

        $bucket = $this->get_instance()->bucket($config->settings_gcs_bucketname);
        $stream = fopen($sourcefile, 'r');
        $options = [
            'name' => $filename,
            'metadata' => [
                'contentType' => $contenttype,
                'contentDisposition' => $contentdisposition,
            ],
        ];
        $bucket->upload($stream, $options);

        $contenthash = pathinfo($filename, PATHINFO_FILENAME);
        $this->report_save($contenthash, "gcs");
    }
}
