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
 * Settings file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    global $CFG, $PAGE;

    $decsep = get_string("decsep", "langconfig");
    $thousandssep = get_string("thousandssep", "langconfig");

    $section = optional_param("section", "", PARAM_ALPHANUMEXT);
    $isthispluginsettings = ($section == "local_alternative_file_system");

    $config = get_config("local_alternative_file_system");
    $settings = new admin_settingpage("local_alternative_file_system", get_string("pluginname", "local_alternative_file_system"));

    $ADMIN->add("localplugins", $settings);

    if ($isthispluginsettings) {
        require_once(__DIR__ . "/settings/tool_objectfs.php");
    }

    if (!empty($CFG->alternative_file_system_class)) {
        $settingsdestinos = [
            "" => get_string("settings_local", "local_alternative_file_system"),
            "s3" => "Amazon S3",
            "space" => "Digital Ocean Space",
            "s3generic" => get_string("settings_s3generic_destino", "local_alternative_file_system"),
        ];
        if ($config->storage_destination == "gcs") {
            $settingsdestinos[] = ["gcs" => "Google Cloud Storage"];
        }

        $settings->add(
            new admin_setting_configselect(
                "local_alternative_file_system/storage_destination",
                get_string("storage_destination", "local_alternative_file_system"),
                get_string("storage_destinationdesc", "local_alternative_file_system"),
                "",
                $settingsdestinos
            )
        );
        $PAGE->requires->js_call_amd("local_alternative_file_system/settings", "init");

        $datalang = [
            "url" => "{$CFG->wwwroot}/local/alternative_file_system",
            "local" => $settingsdestinos[$config->storage_destination],
        ];

        if ($isthispluginsettings) {
            if (in_array($config->storage_destination, ["s3", "space", "s3generic"])) {
                require_once(__DIR__ . "/settings/s3.php");
            }
            if ($config->storage_destination == "gcs") {
                require_once(__DIR__ . "/settings/gcs.php");
            }
        }

    } else {
        $setting = new admin_setting_heading(
            "local_alternative_file_system/header4",
            get_string("instruction_title", "local_alternative_file_system"),
            get_string("instruction_install", "local_alternative_file_system")
        );
        $settings->add($setting);
    }
}
