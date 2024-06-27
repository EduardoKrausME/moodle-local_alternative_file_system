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

namespace local_alternative_file_system;

use dml_exception;
use Exception;
use file_exception;
use file_system;
use file_system_filedir;
use stored_file;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once("{$CFG->dirroot}/lib/filestorage/file_system_filedir.php");

/**
 * external_file_system file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external_file_system extends file_system implements i_file_system {

    /** @var \local_alternative_file_system\storages\s3\s3_file_system */
    private $filesysteminstance = null;

    /**
     * external_file_system constructor.
     *
     * @throws dml_exception
     * @throws file_exception
     */
    public function __construct() {
        $config = get_config("local_alternative_file_system");

        if ($config->settings_destino == 's3') {
            $this->filesysteminstance = new \local_alternative_file_system\storages\s3\s3_file_system();
        } else if ($config->settings_destino == 'space') {
            $this->filesysteminstance = new \local_alternative_file_system\storages\s3\s3_file_system();
        } else if ($config->settings_destino == 'gcs') {
            $this->filesysteminstance = new \local_alternative_file_system\storages\s3\gcs_file_system();
        } else {
            $this->filesysteminstance = new file_system_filedir();
        }
    }

    /**
     * Test config function.
     *
     * @throws dml_exception
     *
     * @throws Exception
     */
    public function test_config() {
        $this->filesysteminstance->test_config();
    }

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
        return $this->filesysteminstance->get_local_path_from_hash($contenthash, $fetchifnotfound);
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
     */
    public function get_remote_path_from_hash($contenthash, $fetchifnotfound = false) {
        return $this->filesysteminstance->get_remote_path_from_hash($contenthash);
    }

    /**
     * get_local_path_from_storedfile function.
     *
     * @param stored_file $file
     * @param bool $fetchifnotfound
     *
     * @return string
     *
     * @throws Exception
     */
    public function get_local_path_from_storedfile(stored_file $file, $fetchifnotfound = false) {
        return $this->get_remote_path_from_hash($file->get_contenthash(), $fetchifnotfound);
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
        return $this->filesysteminstance->get_remote_file_size($contenthash);
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
//        echo '<pre>';
//        print_r($file);
//        print_r($target);
//        die();
//        echo '</pre>';
        return $this->filesysteminstance->copy_content_from_storedfile($file, $target);
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
        return $this->filesysteminstance->remove_file($contenthash);
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
        return $this->filesysteminstance->add_file_from_path($pathname, $contenthash);
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
        $this->filesysteminstance->upload($sourcefile, $filename, $contenttype, $contentdisposition);
    }

    /**
     * Add string content to sha1 pool.
     *
     * @param string $content file content - binary string
     *
     * @return array (contenthash, filesize, newfile)
     *
     * @throws file_exception
     * @throws dml_exception
     */
    public function add_file_from_string($content) {
        return $this->filesysteminstance->add_file_from_string($content);
    }

    /**
     * readfile function.
     *
     * @param stored_file $file
     *
     * @throws file_exception
     * @throws Exception
     */
    public function readfile(stored_file $file) {
        $this->filesysteminstance->readfile($file);
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
        return $this->filesysteminstance->is_file_readable_remotely_by_storedfile($file);
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
        return $this->filesysteminstance->get_imageinfo($file);
    }

    /**
     * Sending count function.
     *
     * @return int
     *
     * @throws dml_exception
     */
    public function sending_count() {
        return $this->filesysteminstance->sending_count();
    }

    /**
     * Missing count function.
     *
     * @return int
     *
     * @throws dml_exception
     */
    public function missing_count() {
        return $this->filesysteminstance->missing_count();
    }
}
