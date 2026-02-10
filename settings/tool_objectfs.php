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
 * Settings tool_objectfs.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

defined('MOODLE_INTERNAL') || die;

if (!empty($CFG->alternative_file_system_class) &&
    strpos($CFG->alternative_file_system_class, '\\tool_objectfs\\') !== false) {
    $reporturl = new moodle_url('/local/alternative_file_system/report-migrate.php');
    $a = (object) ["currentclass" => $CFG->alternative_file_system_class];
    if ($CFG->alternative_file_system_class == '\tool_objectfs\digitalocean_file_system') {
        $a->settings_local = $a->local = "Digital Ocean Space";
        $a->settings_s3_region = get_config("tool_objectfs", "do_region");
        $a->settings_s3_credentials_key = get_config("tool_objectfs", "do_key");
        $a->settings_s3_credentials_secret = get_config("tool_objectfs", "do_secret");
        $a->settings_bucketname = get_config("tool_objectfs", "do_space");
        $a->settings_path = get_config("tool_objectfs", "do_prefix");

        $a->settings_local_lang = get_string("storage_destination", "local_alternative_file_system");
        $a->settings_s3_region_lang = get_string("settings_s3_region", "local_alternative_file_system", $a);
        $a->settings_s3_credentials_key_lang =
            get_string("settings_s3_credentials_key", "local_alternative_file_system", $a);
        $a->settings_s3_credentials_secret_lang =
            get_string("settings_s3_credentials_secret", "local_alternative_file_system", $a);
        $a->settings_bucketname_lang = get_string("settings_bucketname", "local_alternative_file_system", $a);
        $a->settings_path_lang = get_string("settings_path", "local_alternative_file_system", $a);
    } else if ($CFG->alternative_file_system_class == '\tool_objectfs\s3_file_system') {
        $a->settings_local = $a->local = "Amazon S3";
        $a->settings_s3_region = get_config("tool_objectfs", "s3_region");
        $a->settings_s3_credentials_key = get_config("tool_objectfs", "s3_key");
        $a->settings_s3_credentials_secret = get_config("tool_objectfs", "s3_secret");
        $a->settings_bucketname = get_config("tool_objectfs", "s3_bucket");
        $a->settings_path = get_config("tool_objectfs", "s3_keyprefix");

        $a->settings_local_lang = get_string("storage_destination", "local_alternative_file_system");
        $a->settings_s3_region_lang = get_string("settings_s3_region", "local_alternative_file_system", $a);
        $a->settings_s3_credentials_key_lang =
            get_string("settings_s3_credentials_key", "local_alternative_file_system", $a);
        $a->settings_s3_credentials_secret_lang =
            get_string("settings_s3_credentials_secret", "local_alternative_file_system", $a);
        $a->settings_bucketname_lang = get_string("settings_bucketname", "local_alternative_file_system", $a);
        $a->settings_path_lang = get_string("settings_path", "local_alternative_file_system", $a);
    }

    if (!isset($config->settings_local[3])) {
        set_config("settings_local", $a->settings_local, "local_alternative_file_system");
    }
    if (!isset($config->settings_s3_region[3])) {
        set_config("settings_s3_region", $a->settings_s3_region, "local_alternative_file_system");
    }
    if (!isset($config->settings_s3_credentials_key[3])) {
        set_config("settings_s3_credentials_key", $a->settings_s3_credentials_key, "local_alternative_file_system");
    }
    if (!isset($config->settings_s3_credentials_secret[3])) {
        set_config("settings_s3_credentials_secret", $a->settings_s3_credentials_secret, "local_alternative_file_system");
    }
    if (!isset($config->settings_bucketname[3])) {
        set_config("settings_bucketname", $a->settings_bucketname, "local_alternative_file_system");
    }
    if (!isset($config->settings_path[3])) {
        set_config("settings_path", $a->settings_path, "local_alternative_file_system");
    }

    $msg = get_string("settings_objectfs_notice", "local_alternative_file_system", $a);

    $settings->add(
        new admin_setting_heading(
            'local_alternative_file_system/objectfs_notice',
            '',
            $PAGE->get_renderer("core")->render(new notification($msg, notification::NOTIFY_INFO, false))
        )
    );
}
