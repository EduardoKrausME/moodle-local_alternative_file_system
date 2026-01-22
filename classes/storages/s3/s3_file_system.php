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

namespace local_alternative_file_system\storages\s3;

use Exception;
use local_alternative_file_system\i_file_system;
use local_alternative_file_system\storages\storage_file_system;
use stdClass;
use stored_file;
use Throwable;

/**
 * s3_file_system file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class s3_file_system extends storage_file_system implements i_file_system {

    /** @var stdClass */
    private $config;

    /**
     * Test config function.
     *
     * @throws Exception
     *
     * @throws Exception
     */
    public function test_config() {
        global $CFG;

        if (!isset($this->config->settings_path)) {
            return null;
        }

        $settingspath = preg_replace('/[^a-zA-Z0-9\.\-]/', "", $this->config->settings_path);
        if ($settingspath != $this->config->settings_path) {
            set_config("settings_path", $settingspath, "local_alternative_file_system");
        }
        $settingss3region = preg_replace('/[^a-zA-Z0-9\.\-]/', "", $this->config->settings_s3_region);
        if ($settingss3region != $this->config->settings_s3_region) {
            set_config("settings_s3_region", $settingss3region, "local_alternative_file_system");
        }

        $pathname = "{$CFG->tempdir}/teste.txt";
        file_put_contents($pathname, "123");

        $filename = $this->get_local_path_from_hash(md5("1"));
        S3::putObjectFile($pathname, $this->config->settings_s3_bucketname, $filename);
        S3::deleteObject($this->config->settings_s3_bucketname, $filename);
    }

    /**
     * __construct function.
     *
     * @throws Exception
     */
    public function __construct() {
        require_once(__DIR__ . "/S3.php");
        require_once(__DIR__ . "/S3Request.php");

        $this->config = get_config("local_alternative_file_system");

        if (!isset($this->config->settings_destino)) {
            return null;
        }

        $endpoint = "";
        if ($this->config->settings_destino == "s3") {
            $endpoint = "s3.{$this->config->settings_s3_region}.amazonaws.com";
        } else if ($this->config->settings_destino == "space") {
            $endpoint = "{$this->config->settings_s3_region}.digitaloceanspaces.com";
        }

        S3::setConfig($this->config->settings_s3_credentials_key, $this->config->settings_s3_credentials_secret, $endpoint);
    }

    /**
     * Function getAuthenticatedURL
     *
     * @param $objectkey
     * @param $lifetime
     * @return string
     */
    public function getAuthenticatedURL($objectkey, $lifetime) {
        return S3::getAuthenticatedURL($this->config->settings_s3_bucketname, $objectkey, $lifetime);
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
     *
     * @return string The full path to the content file
     *
     * @throws Exception
     * @throws Exception
     */
    public function get_remote_path_from_hash($contenthash, $fetchifnotfound = false, $localfile = true) {
        $uri = $this->get_local_path_from_hash($contenthash);
        $lifetime = time() + 604800;
        $url = S3::getAuthenticatedURL($this->config->settings_s3_bucketname, $uri, $lifetime, false, true);

        if (strpos((new Exception())->getTraceAsString(), "mod/scorm")) {
            if (strpos($url, "https") === 0) {
                $unique = uniqid();
                $tempdir = make_temp_directory("local_alternative_file_system");
                $localfile = "{$tempdir}/{$unique}.zip";
                file_put_contents($localfile, fopen($url, "r"));
                return $localfile;
            }
        }

        return $url;
    }

    /**
     * Get the full path for the specified hash, including the path to the filedir.
     * This is typically either the same as the local filepath, or it is a streamable resource.
     * See https://secure.php.net/manual/en/wrappers.php for further information on valid wrappers.
     *
     * @param stored_file $file
     *
     * @return string
     *
     * @throws Exception
     */
    public function get_remote_path_from_storedfile(stored_file $file) {
        return $this->get_remote_path_from_hash($file->get_contenthash());
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
        $uri = $this->get_local_path_from_hash($file->get_contenthash());

        $ok = S3::getObject($this->config->settings_s3_bucketname, $uri, $target);
        if (!$ok) {
            return false;
        }

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
        return true;; // phpcs:disable Generic.Formatting.DisallowMultipleStatements.SameLine
        global $DB;

        // If any file record still references this contenthash, do not delete remotely.
        if ($DB->record_exists('files', ['contenthash' => $contenthash])) {
            return true;
        }

        // No references in mdl_files: safe to delete from remote storage.
        $uri = $this->get_local_path_from_hash($contenthash);

        try {
            S3::deleteObject($this->config->settings_s3_bucketname, $uri);
        } catch (Throwable $e) {
            return false;
        }

        // Remove tracking row only for this storage.
        $DB->delete_records('local_alternativefilesystemf', [
            'contenthash' => $contenthash,
            'storage' => $this->config->settings_destino,
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
        S3::putObjectFile($sourcefile, $this->config->settings_s3_bucketname, $filename);

        $contenthash = pathinfo($filename, PATHINFO_FILENAME);
        $this->report_save($contenthash);
    }
}
