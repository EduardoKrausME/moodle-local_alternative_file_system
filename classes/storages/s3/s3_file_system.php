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

use dml_exception;
use Exception;
use local_alternative_file_system\i_file_system;
use local_alternative_file_system\storages\storage_file_system;
use stored_file;

/**
 * s3_file_system file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class s3_file_system extends storage_file_system implements i_file_system {

    /**
     * Test config function.
     *
     * @throws dml_exception
     *
     * @throws Exception
     */
    public function test_config() {
        global $CFG;

        $this->get_instance();

        $config = get_config("local_alternative_file_system");
        if (!isset($config->settings_path)) {
            return null;
        }

        $settingspath = preg_replace('/[^a-zA-Z0-9\.\-]/', "", $config->settings_path);
        if ($settingspath != $config->settings_path) {
            set_config("settings_path", $settingspath, "local_alternative_file_system");
        }
        $settingss3region = preg_replace('/[^a-zA-Z0-9\.\-]/', "", $config->settings_s3_region);
        if ($settingss3region != $config->settings_s3_region) {
            set_config("settings_s3_region", $settingss3region, "local_alternative_file_system");
        }

        $pathname = "{$CFG->tempdir}/teste.txt";
        file_put_contents($pathname, "123");

        $filename = $this->get_local_path_from_hash(md5("1"));
        $this->get_instance()->putObjectFile($pathname, $config->settings_s3_bucketname, $filename, $acl = S3::ACL_PRIVATE);
        $this->get_instance()->deleteObject($config->settings_s3_bucketname, $filename);
    }

    /**
     * get_instance function.
     *
     * @return S3
     *
     * @throws Exception
     */
    private function get_instance() {
        static $s3client = null;
        if ($s3client) {
            return $s3client;
        }

        require_once(__DIR__ . "/S3.php");
        require_once(__DIR__ . "/S3Request.php");

        $config = get_config("local_alternative_file_system");

        if (!isset($config->settings_destino)) {
            return null;
        }

        $endpoint = "";
        if ($config->settings_destino == "s3") {
            $endpoint = "s3.{$config->settings_s3_region}.amazonaws.com";
        } else if ($config->settings_destino == "space") {
            $endpoint = "{$config->settings_s3_region}.digitaloceanspaces.com";
        }

        $s3client = new S3($config->settings_s3_credentials_key, $config->settings_s3_credentials_secret, $endpoint);

        return $s3client;
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
     * @throws dml_exception
     * @throws Exception
     */
    public function get_remote_path_from_hash($contenthash, $fetchifnotfound = false, $localfile = true) {
        $config = get_config("local_alternative_file_system");

        $uri = $this->get_local_path_from_hash($contenthash);
        $lifetime = time() + 604800;
        $url = $this->get_instance()->getAuthenticatedURL($config->settings_s3_bucketname, $uri, $lifetime, false, true);

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
     * @throws dml_exception
     */
    public function get_remote_path_from_storedfile(stored_file $file) {
        return $this->get_remote_path_from_hash($file->get_contenthash());
    }

    /**
     * Copy content of file to given pathname.
     *
     * @param stored_file $file The file to be copied
     * @param string $target real path to the new file
     *
     * @return bool success
     *
     * @throws dml_exception
     */
    public function copy_content_from_storedfile(stored_file $file, $target) {
        $fileurl = $this->get_remote_path_from_hash($file->get_contenthash(), false, false);
        $filecontent = file_get_contents($fileurl);
        return file_put_contents($target, $filecontent);
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
        return true;
    }

    /**
     * upload function.
     *
     * @param string $sourcefile
     * @param string $filename
     * @param string $contenttype
     * @param string $contentdisposition
     *
     * @throws Exception
     */
    public function upload($sourcefile, $filename, $contenttype, $contentdisposition) {
        $config = get_config("local_alternative_file_system");
        $s3client = $this->get_instance();

        $s3client->putObjectFile($sourcefile, $config->settings_s3_bucketname, $filename, S3::ACL_PRIVATE);

        $contenthash = pathinfo($filename, PATHINFO_FILENAME);
        $this->report_save($contenthash);
    }
}
