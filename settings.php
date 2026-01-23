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

use core\output\notification;
use local_alternative_file_system\storages\gcs\gcs_file_system;
use local_alternative_file_system\storages\s3\s3_file_system;

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    global $CFG, $PAGE;

    $decsep = get_string("decsep", "langconfig");
    $thousandssep = get_string("thousandssep", "langconfig");

    $section = optional_param("section", '', PARAM_ALPHANUMEXT);
    $isthispluginsettings = ($section == "local_alternative_file_system");

    $config = get_config("local_alternative_file_system");
    $settings = new admin_settingpage("local_alternative_file_system", get_string("pluginname", "local_alternative_file_system"));

    $ADMIN->add("localplugins", $settings);

    if ($isthispluginsettings) {
        if (!empty($CFG->alternative_file_system_class) && strpos($CFG->alternative_file_system_class, '\\tool_objectfs\\') !== false) {
            $reporturl = new moodle_url('/local/alternative_file_system/report-migrate.php');
            $a = (object) ["currentclass" => $CFG->alternative_file_system_class];
            if ($CFG->alternative_file_system_class == '\tool_objectfs\digitalocean_file_system') {
                $a->settings_local = $a->local = "Digital Ocean Space";
                $a->settings_s3_region = get_config("tool_objectfs", "do_region");
                $a->settings_s3_credentials_key = get_config("tool_objectfs", "do_key");
                $a->settings_s3_credentials_secret = get_config("tool_objectfs", "do_secret");
                $a->settings_bucketname = get_config("tool_objectfs", "do_space");
                $a->settings_path = get_config("tool_objectfs", "do_prefix");

                $a->settings_local_lang =  get_string("settings_destino", "local_alternative_file_system");
                $a->settings_s3_region_lang =  get_string("settings_s3_region", "local_alternative_file_system", $a);
                $a->settings_s3_credentials_key_lang =  get_string("settings_s3_credentials_key", "local_alternative_file_system", $a);
                $a->settings_s3_credentials_secret_lang =  get_string("settings_s3_credentials_secret", "local_alternative_file_system", $a);
                $a->settings_bucketname_lang =  get_string("settings_bucketname", "local_alternative_file_system", $a);
                $a->settings_path_lang =  get_string("settings_path", "local_alternative_file_system", $a);
            }else if ($CFG->alternative_file_system_class == '\tool_objectfs\s3_file_system') {
                $a->settings_local = $a->local = "Amazon S3";
                $a->settings_s3_region = get_config("tool_objectfs", "s3_region");
                $a->settings_s3_credentials_key = get_config("tool_objectfs", "s3_key");
                $a->settings_s3_credentials_secret = get_config("tool_objectfs", "s3_secret");
                $a->settings_bucketname = get_config("tool_objectfs", "s3_bucket");
                $a->settings_path = get_config("tool_objectfs", "s3_keyprefix");

                $a->settings_local_lang =  get_string("settings_destino", "local_alternative_file_system");
                $a->settings_s3_region_lang =  get_string("settings_s3_region", "local_alternative_file_system", $a);
                $a->settings_s3_credentials_key_lang =  get_string("settings_s3_credentials_key", "local_alternative_file_system", $a);
                $a->settings_s3_credentials_secret_lang =  get_string("settings_s3_credentials_secret", "local_alternative_file_system", $a);
                $a->settings_bucketname_lang =  get_string("settings_bucketname", "local_alternative_file_system", $a);
                $a->settings_path_lang =  get_string("settings_path", "local_alternative_file_system", $a);
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

            $settings->add(new admin_setting_heading(
                'local_alternative_file_system/objectfs_notice',
                '',
                $PAGE->get_renderer("core")->render(new notification($msg, notification::NOTIFY_INFO, false))
            ));
        }
    }

    if (!empty($CFG->alternative_file_system_class)) {
        $settingsdestinos = [
            "" => get_string("settings_local", "local_alternative_file_system"),
            "s3" => "Amazon S3",
            "space" => "Digital Ocean Space",
        ];
        if ($config->settings_destino == "gcs") {
            $settingsdestinos[] = ["gcs" => "Google Cloud Storage"];
        }

        $settings->add(new admin_setting_configselect(
            "local_alternative_file_system/settings_destino",
            get_string("settings_destino", "local_alternative_file_system"),
            get_string("settings_destinodesc", "local_alternative_file_system"),
            "",
            $settingsdestinos
        ));
        $PAGE->requires->js_call_amd("local_alternative_file_system/settings", "init");

        $datalang = [
            "url" => "{$CFG->wwwroot}/local/alternative_file_system",
            "local" => $settingsdestinos[$config->settings_destino],
        ];

        if ($isthispluginsettings && $config->settings_destino == "s3" || $config->settings_destino == "space") {
            if ($config->settings_destino == "s3") {
                $datalang["ex_region"] = "us-east-1";
            } else if ($config->settings_destino == "space") {
                $datalang["ex_region"] = "nyc1";
            }

            $s3filesystem = new s3_file_system();
            try {
                $s3filesystem->test_config();

                $string = get_string("settings_success", "local_alternative_file_system");
                $setting = new admin_setting_heading("local_alternative_file_system/header1", "",
                    $PAGE->get_renderer("core")->render(new notification($string, notification::NOTIFY_SUCCESS, false)));
                $settings->add($setting);

                $missing_count = $s3filesystem->missing_count();
                $sending_count = $s3filesystem->sending_count();
                if ($missing_count != $sending_count) {
                    $a = [
                        "missing" => number_format($missing_count, 0, $decsep, $thousandssep),
                        "sending" => number_format($sending_count, 0, $decsep, $thousandssep),
                    ];
                    $string1 = get_string("migrate_total", "local_alternative_file_system", $a);
                    $string2 = get_string("settings_migrate_remote", "local_alternative_file_system", $datalang);
                    $string3 = get_string("settings_migrate_local", "local_alternative_file_system", $datalang);
                    $setting = new admin_setting_heading("local_alternative_file_system/header2", "",
                        $PAGE->get_renderer("core")->render(new notification($string1, notification::NOTIFY_WARNING, false)) .
                        $PAGE->get_renderer("core")->render(new notification("{$string2}<br>{$string3}", notification::NOTIFY_INFO, false))
                    );
                    $settings->add($setting);
                } else {
                    $string3 = get_string("settings_migrate_local", "local_alternative_file_system", $datalang);
                    $setting = new admin_setting_heading("local_alternative_file_system/header2", "",
                        $PAGE->get_renderer("core")->render(new notification($string3, notification::NOTIFY_INFO, false)));
                    $settings->add($setting);
                }

            } catch (Exception $e) {
                $setting = new admin_setting_heading("local_alternative_file_system/header3",
                    "",
                    $PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR)));
                $settings->add($setting);
            }

            $setting = new admin_setting_configtext(
                "local_alternative_file_system/settings_s3_region",
                get_string("settings_s3_region", "local_alternative_file_system", $datalang),
                get_string("settings_s3_regiondesc", "local_alternative_file_system", $datalang),
                "", PARAM_TEXT);
            $settings->add($setting);

            $setting = new admin_setting_configtext(
                "local_alternative_file_system/settings_s3_credentials_key",
                get_string("settings_s3_credentials_key", "local_alternative_file_system", $datalang),
                get_string("settings_s3_credentials_keydesc", "local_alternative_file_system", $datalang),
                "", PARAM_TEXT);
            $settings->add($setting);

            $setting = new admin_setting_configtext(
                "local_alternative_file_system/settings_s3_credentials_secret",
                get_string("settings_s3_credentials_secret", "local_alternative_file_system", $datalang),
                get_string("settings_s3_credentials_secretdesc", "local_alternative_file_system", $datalang),
                "", PARAM_TEXT);
            $settings->add($setting);

            $setting = new admin_setting_configtext(
                "local_alternative_file_system/settings_s3_bucketname",
                get_string("settings_bucketname", "local_alternative_file_system", $datalang),
                get_string("settings_bucketnamedesc", "local_alternative_file_system", $datalang),
                "", PARAM_TEXT);
            $settings->add($setting);

            $setting = new admin_setting_configtext(
                "local_alternative_file_system/settings_path",
                get_string("settings_path", "local_alternative_file_system", $datalang),
                get_string("settings_pathdesc", "local_alternative_file_system", $datalang),
                "", PARAM_TEXT);
            $settings->add($setting);
        }
        if ($config->settings_destino == "gcs") {

            $gcsfilesystem = new gcs_file_system();
            try {
                $gcsfilesystem->test_config();

                $string = get_string("settings_success", "local_alternative_file_system");
                $setting = new admin_setting_heading("local_alternative_file_system/header1", "",
                    $PAGE->get_renderer("core")->render(new notification($string, notification::NOTIFY_SUCCESS, false)));
                $settings->add($setting);

                if ($gcsfilesystem->missing_count()) {
                    $a = [
                        "missing" => number_format($gcsfilesystem->missing_count(), 0, $decsep, $thousandssep),
                        "sending" => number_format($gcsfilesystem->sending_count(), 0, $decsep, $thousandssep),
                    ];
                    $string1 = get_string("migrate_total", "local_alternative_file_system", $a);
                    $string2 = get_string("settings_migrate_remote", "local_alternative_file_system", $datalang);
                    $string3 = get_string("settings_migrate_local", "local_alternative_file_system", $datalang);
                    $setting = new admin_setting_heading("local_alternative_file_system/header2", "",
                        $PAGE->get_renderer("core")->render(new notification($string1, notification::NOTIFY_WARNING, false)) .
                        $PAGE->get_renderer("core")->render(new notification("{$string2}<br>{$string3}", notification::NOTIFY_INFO, false))
                    );
                    $settings->add($setting);
                } else {
                    $a = [
                        "missing" => number_format($gcsfilesystem->missing_count(), 0, $decsep, $thousandssep),
                        "sending" => number_format($gcsfilesystem->sending_count(), 0, $decsep, $thousandssep),
                    ];
                    $string1 = get_string("migrate_total", "local_alternative_file_system", $a);
                    $setting = new admin_setting_heading("local_alternative_file_system/header2", "",
                        $PAGE->get_renderer("core")->render(new notification($string1, notification::NOTIFY_SUCCESS, false)));
                    $settings->add($setting);
                }

            } catch (Exception $e) {
                $setting = new admin_setting_heading("local_alternative_file_system/header3",
                    "",
                    $PAGE->get_renderer("core")->render(new notification($e->getMessage(), notification::NOTIFY_ERROR)));
                $settings->add($setting);
            }

            $setting = new admin_setting_configtextarea(
                "local_alternative_file_system/settings_gcs_keyfile",
                get_string("settings_gcs_keyfile", "local_alternative_file_system", $datalang),
                get_string("settings_gcs_keyfiledesc", "local_alternative_file_system", $datalang),
                "", PARAM_TEXT);
            $settings->add($setting);

            $setting = new admin_setting_configtext(
                "local_alternative_file_system/settings_gcs_bucketname",
                get_string("settings_bucketname", "local_alternative_file_system", $datalang),
                get_string("settings_bucketnamedesc", "local_alternative_file_system", $datalang),
                "", PARAM_TEXT);
            $settings->add($setting);

            $setting = new admin_setting_configtext(
                "local_alternative_file_system/settings_path",
                get_string("settings_path", "local_alternative_file_system", $datalang),
                get_string("settings_pathdesc", "local_alternative_file_system", $datalang),
                "", PARAM_TEXT);
            $settings->add($setting);
        }

    } else {
        $setting = new admin_setting_heading("local_alternative_file_system/header4",
            get_string("instruction_title", "local_alternative_file_system"),
            get_string("instruction_install", "local_alternative_file_system"));
        $settings->add($setting);
    }
}
