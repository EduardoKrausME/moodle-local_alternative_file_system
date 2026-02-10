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
 * Settings GCS.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use local_alternative_file_system\storages\gcs\gcs_file_system;

defined('MOODLE_INTERNAL') || die;

$gcsfilesystem = new gcs_file_system();
try {
    $gcsfilesystem->test_config();

    $string = get_string("settings_success", "local_alternative_file_system");
    $setting = new admin_setting_heading(
        "local_alternative_file_system/header1", "",
        $PAGE->get_renderer("core")->render(new notification($string, notification::NOTIFY_SUCCESS, false))
    );
    $settings->add($setting);

    if ($gcsfilesystem->missing_count()) {
        $a = [
            "missing" => number_format($gcsfilesystem->missing_count(), 0, $decsep, $thousandssep),
            "sending" => number_format($gcsfilesystem->sending_count(), 0, $decsep, $thousandssep),
        ];
        $string1 = get_string("migrate_total", "local_alternative_file_system", $a);
        $string2 = get_string("settings_migrate_remote", "local_alternative_file_system", $datalang);
        $string3 = get_string("settings_migrate_local", "local_alternative_file_system", $datalang);
        $setting = new admin_setting_heading(
            "local_alternative_file_system/header2", "",
            $PAGE->get_renderer("core")->render(new notification($string1, notification::NOTIFY_WARNING, false)) .
            $PAGE->get_renderer("core")->render(
                new notification("{$string2}<br>{$string3}", notification::NOTIFY_INFO, false)
            )
        );
        $settings->add($setting);
    } else {
        $a = [
            "missing" => number_format($gcsfilesystem->missing_count(), 0, $decsep, $thousandssep),
            "sending" => number_format($gcsfilesystem->sending_count(), 0, $decsep, $thousandssep),
        ];
        $string1 = get_string("migrate_total", "local_alternative_file_system", $a);
        $setting = new admin_setting_heading(
            "local_alternative_file_system/header2", "",
            $PAGE->get_renderer("core")->render(new notification($string1, notification::NOTIFY_SUCCESS, false))
        );
        $settings->add($setting);
    }

} catch (Exception $e) {
    $setting = new admin_setting_heading(
        "local_alternative_file_system/header3",
        "",
        $PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR))
    );
    $settings->add($setting);
}

$setting = new admin_setting_configtextarea(
    "local_alternative_file_system/settings_gcs_keyfile",
    get_string("settings_gcs_keyfile", "local_alternative_file_system", $datalang),
    get_string("settings_gcs_keyfiledesc", "local_alternative_file_system", $datalang),
    "", PARAM_TEXT
);
$settings->add($setting);

$setting = new admin_setting_configtext(
    "local_alternative_file_system/settings_gcs_bucketname",
    get_string("settings_bucketname", "local_alternative_file_system", $datalang),
    get_string("settings_bucketnamedesc", "local_alternative_file_system", $datalang),
    "", PARAM_TEXT
);
$settings->add($setting);

$setting = new admin_setting_configtext(
    "local_alternative_file_system/settings_path",
    get_string("settings_path", "local_alternative_file_system", $datalang),
    get_string("settings_pathdesc", "local_alternative_file_system", $datalang),
    "", PARAM_TEXT
);
$settings->add($setting);
