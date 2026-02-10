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
 * Settings S3.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use local_alternative_file_system\storages\s3\s3_file_system;

defined('MOODLE_INTERNAL') || die;

if ($config->storage_destination == "s3") {
    $datalang["ex_region"] = "us-east-1";
} else if ($config->storage_destination == "space") {
    $datalang["ex_region"] = "nyc1";
} else if ($config->storage_destination == "s3generic") {
    $datalang["ex_region"] = "us-east-1";
}

$s3filesystem = new s3_file_system();
try {
    $s3filesystem->test_config();

    $missingcount = $s3filesystem->missing_count();
    $sendingcount = $s3filesystem->sending_count();
    if ($missingcount != $sendingcount) {
        $a = [
            "missing" => number_format($missingcount, 0, $decsep, $thousandssep),
            "sending" => number_format($sendingcount, 0, $decsep, $thousandssep),
        ];
        $migratetotalstring = get_string("migrate_total", "local_alternative_file_system", $a);
        $migratetotalstring .= get_string("settings_migrate_remote", "local_alternative_file_system", $datalang);
        $message = $PAGE->get_renderer("core")->render(new notification($migratetotalstring, notification::NOTIFY_WARNING, false));
        $setting = new admin_setting_heading("local_alternative_file_system/header2", "", $message);
        $settings->add($setting);
    }

    $successstring = get_string("settings_success", "local_alternative_file_system");
    $successstring .= "<br><br>";
    $successstring .= get_string("settings_migrate_local", "local_alternative_file_system", $datalang);
    $setting = new admin_setting_heading(
        "local_alternative_file_system/header1", "",
        $PAGE->get_renderer("core")->render(new notification($successstring, notification::NOTIFY_SUCCESS, false))
    );
    $settings->add($setting);

} catch (Exception $e) {
    if ($e->getMessage() != "settings_empty") {
        $setting = new admin_setting_heading(
            "local_alternative_file_system/header3",
            "",
            $PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR))
        );
        $settings->add($setting);
    }
}

if (in_array($config->storage_destination, [ "s3generic"])) {
    $setting = new admin_setting_configtext(
        "local_alternative_file_system/settings_s3generic_endpoint",
        get_string("settings_s3generic_endpoint", "local_alternative_file_system"),
        get_string("settings_s3generic_endpoint_desc", "local_alternative_file_system"),
        "",
        PARAM_RAW_TRIMMED
    );
    $settings->add($setting);
}

$setting = new admin_setting_configtext(
    "local_alternative_file_system/settings_s3_region",
    get_string("settings_s3_region", "local_alternative_file_system", $datalang),
    get_string("settings_s3_regiondesc", "local_alternative_file_system", $datalang),
    "", PARAM_TEXT
);
$settings->add($setting);

$setting = new admin_setting_configtext(
    "local_alternative_file_system/settings_s3_credentials_key",
    get_string("settings_s3_credentials_key", "local_alternative_file_system", $datalang),
    get_string("settings_s3_credentials_keydesc", "local_alternative_file_system", $datalang),
    "", PARAM_TEXT
);
$settings->add($setting);

$setting = new admin_setting_configtext(
    "local_alternative_file_system/settings_s3_credentials_secret",
    get_string("settings_s3_credentials_secret", "local_alternative_file_system", $datalang),
    get_string("settings_s3_credentials_secretdesc", "local_alternative_file_system", $datalang),
    "", PARAM_TEXT
);
$settings->add($setting);

$setting = new admin_setting_configtext(
    "local_alternative_file_system/settings_s3_bucketname",
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
