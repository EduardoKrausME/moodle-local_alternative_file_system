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

namespace local_alternative_file_system\storages;

use dml_exception;
use Exception;
use file_exception;
use file_system;
use stored_file;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once("{$CFG->dirroot}/lib/filestorage/file_system.php");

/**
 * storage_file_system file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class storage_file_system extends file_system {

    /**
     * get_local_path_from_hash function.
     *
     * @param string $contenthash
     * @param bool $fetchifnotfound
     *
     * @return string
     *
     * @throws dml_exception
     */
    public function get_local_path_from_hash($contenthash, $fetchifnotfound = false) {
        $paths = [];

        $config = get_config("local_alternative_file_system");
        if (isset($config->settings_path[2])) {
            $paths[] = $config->settings_path;
        }

        $paths[] = substr($contenthash, 0, 2);
        $paths[] = substr($contenthash, 2, 2);
        $paths[] = $contenthash;

        return implode("/", $paths);
    }

    /**
     * Add string content to sha1 pool.
     *
     * @param string $content file content - binary string
     *
     * @return array (contenthash, filesize, newfile)
     *
     * @throws Exception
     */
    public function add_file_from_string($content) {
        global $CFG;

        $contenthash = sha1($content);
        $filesize = strlen($content);

        if ($content === '') {
            return [$contenthash, $filesize, false];
        }

        $pathname = "{$CFG->tempdir}/{$contenthash}";
        file_put_contents($pathname, $content);
        $upload = $this->add_file_from_path($pathname, $contenthash);

        unlink($pathname);
        return $upload;
    }


    /**
     * readfile function.
     *
     * @param stored_file $file
     *
     * @throws file_exception
     * @throws Exception
     */
    public function readfile(\stored_file $file) {
        $url = $this->get_remote_path_from_hash($file->get_contenthash());

        if ($file->get_filesize() < 1000) {
            $success = readfile($url);
        } else {
            $success = readfile_allow_large($url, $file->get_filesize());
        }

        if (!$success) {
            throw new file_exception('storedfilecannotreadfile', $file->get_filename());
        }
    }

    /**
     * Determine whether the file is present on the local file system somewhere.
     *
     * @param stored_file $file The file to ensure is available.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function is_file_readable_remotely_by_storedfile(stored_file $file) {
        if (!$file->get_filesize()) {
            return true;
        }

        // Aqui corrigir.
        return true;
    }

    /**
     * Returns information about image.
     * Information is determined from the file content
     *
     * @param stored_file $file The file to inspect
     *
     * @return mixed array with width, height and mimetype; false if not an image
     *
     * @throws \coding_exception
     * @throws Exception
     */
    public function get_imageinfo(stored_file $file) {
        if (!$this->is_image_from_storedfile($file)) {
            return false;
        }

        $hash = $file->get_contenthash();
        $cache = \cache::make('core', 'file_imageinfo');
        $info = $cache->get($hash);
        if ($info !== false) {
            return $info;
        }

        $path = $this->get_remote_path_from_hash($file->get_contenthash());
        $info = $this->get_imageinfo_from_path($path);
        $cache->set($hash, $info);
        return $info;
    }

    /**
     * get_remote_file_size function.
     *
     * @param string $contenthash
     *
     * @return int
     *
     * @throws Exception
     */
    public function get_remote_file_size($contenthash) {

        $url = $this->get_remote_path_from_hash($contenthash);
        $curl = curl_init($url);

        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($curl);
        curl_close($curl);

        if ($data) {
            if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
                $status = (int)$matches[1];
                if ($status != 200 || ($status > 300 && $status <= 308)) {
                    return 0;
                }
            }

            if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
                return (int)$matches[1];
            }
        }

        return 0;
    }

    /**
     * report_save function.
     *
     * @param string $contenthash
     *
     * @return bool
     *
     * @throws dml_exception
     */
    public function report_save($contenthash) {
        global $DB;

        $config = get_config('local_alternative_file_system');

        $data = [
            "contenthash" => $contenthash,
            "storage" => $config->settings_destino,
            "timemodifield" => time(),
        ];
        try {
            $DB->insert_record("local_alternative_file_system_file", $data);
            return true;
        } catch (dml_exception $e) {
            return false;
        }
    }

    /**
     * Sending count function.
     *
     * @return int
     *
     * @throws dml_exception
     */
    public function sending_count() {
        global $DB;

        $config = get_config('local_alternative_file_system');

        $sql = "SELECT COUNT(contenthash) AS num_files
                  FROM {local_alternative_file_system_file}
                 WHERE storage = '{$config->settings_destino}'";
        $result = $DB->get_record_sql($sql);
        return $result->num_files;
    }

    /**
     * Missing count function.
     *
     * @return int
     *
     * @throws dml_exception
     */
    public function missing_count() {
        global $DB;

        $config = get_config('local_alternative_file_system');

        $sql = "SELECT COUNT(*) AS num_files
                  FROM {files}
                 WHERE contenthash NOT IN (
                        SELECT contenthash
                          FROM {local_alternative_file_system_file}
                         WHERE storage = '{$config->settings_destino}'
                     )
                   AND filename    LIKE '__%'
                   AND filesize    > 2
                   AND mimetype    IS NOT NULL";
        $result = $DB->get_record_sql($sql);
        return $result->num_files;
    }

    /**
     * Add file content to sha1 pool.
     *
     * @param string $pathname Path to file currently on disk
     * @param string $contenthash SHA1 hash of content if known (performance only)
     *
     * @return array (contenthash, filesize, newfile)
     *
     * @throws file_exception
     * @throws dml_exception
     * @throws Exception
     */
    public function add_file_from_path($pathname, $contenthash = null) {
        if (!is_readable($pathname)) {
            throw new file_exception('storedfilecannotread', '', $pathname);
        }

        $filesize = filesize($pathname);
        if ($filesize === false) {
            throw new file_exception('storedfilecannotread', '', $pathname);
        }
        if (is_null($contenthash)) {
            $contenthash = sha1_file($pathname);
        }
        if (is_null($contenthash)) {
            throw new file_exception('storedfilecannotread', '', $pathname);
        }

        $contenttype = 'binary/octet-stream';
        $contentdisposition = "attachment";
        foreach ($_FILES as $file) {
            if ($file['tmp_name'] == $pathname) {
                $contentdisposition = "inline; filename={$file['name']}";
                $contenttype = $file['type'];
            }
        }

        $filename = $this->get_local_path_from_hash($contenthash);
        $this->upload($pathname, $filename, $contenttype, $contentdisposition);

        return [$contenthash, $filesize, $filename];
    }

    /**
     * get_local_path_from_storedfile function.
     *
     * @param stored_file $file
     * @param bool $fetchifnotfound
     *
     * @throws Exception
     */
    public function get_local_path_from_storedfile(stored_file $file, $fetchifnotfound = false) {
        // Implemented in storage_file_system.php.
    }

    /**
     * Get the full path for the specified hash, including the path to the filedir.
     *
     * This is typically either the same as the local filepath, or it is a streamable resource.
     *
     * See https://secure.php.net/manual/en/wrappers.php for further information on valid wrappers.
     *
     * @param string $contenthash
     *
     * @return string The full path to the content file
     *
     * @throws dml_exception
     */
    public function get_remote_path_from_hash($contenthash) {
        // Implemented in storage_file_system.php.
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
        // Implemented in storage_file_system.php.
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
        // Implemented in storage_file_system.php.
    }
}
