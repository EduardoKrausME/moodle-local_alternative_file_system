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
 * Lang en file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['instruction_install'] = '<p><strong>Add the line below to the Moodle <code>config.php</code> file:</strong></p><pre><code>$CFG->alternative_file_system_class = \'\local_alternative_file_system\external_file_system\';</code></pre><p><strong>Important:</strong> Insert this line <strong>before</strong> the following line, if it exists in your file:</p><pre><code>require_once(__DIR__ . \'/lib/setup.php\');</code></pre>';
$string['instruction_title'] = 'Installation Instructions';

$string['migrate_link'] = '<p><a class="btn btn-success" href="?execute=1">Execute Now (may take a long time)</a></p>';
$string['migrate_title'] = 'Migrate Local to Remote Storage';
$string['migrate_total'] = '<p>You have <strong>{$a->missing}</strong> local files awaiting migration, while <strong>{$a->sending}</strong> files have already been migrated to the remote environment.</p>';

$string['pluginname'] = 'Alternative File System';

$string['privacy:no_data_reason'] = 'The Alternative File System plugin does not store any personal data.';

$string['settings_bucketname'] = '{$a->local} Bucket Name';
$string['settings_bucketnamedesc'] = 'The unique name assigned to the bucket in {$a->local}.';

$string['settings_destino'] = 'Storage Destination';
$string['settings_destinodesc'] = 'Choose the storage destination and save to load storage-related data.';

$string['settings_gcs_keyfile'] = 'Google-storage.json Content';
$string['settings_gcs_keyfiledesc'] = 'Paste here the content of the "google-storage.json" file.';

$string['settings_local'] = 'Local files in Moodle';

$string['settings_migrate'] = 'Use the service <a target="_blank" href="{$a->url}">move-to-external.php</a> to migrate local data to {$a->local}.';

$string['settings_path'] = '{$a->local} Object Path';
$string['settings_pathdesc'] = 'The path within the bucket where objects will be stored. Only letters and numbers are accepted.';

$string['settings_s3_credentials_key'] = '{$a->local} Access Key';
$string['settings_s3_credentials_keydesc'] = 'The access key used to authenticate with the {$a->local} service.';
$string['settings_s3_credentials_secret'] = '{$a->local} Secret Key';
$string['settings_s3_credentials_secretdesc'] = 'The secret key used to authenticate with the {$a->local} service.';

$string['settings_s3_region'] = '{$a->local} Region';
$string['settings_s3_regiondesc'] = 'The region where the {$a->local} bucket is located, for example, "{$a->ex_region}".';

$string['settings_success'] = '<h2>Data is correct.</h2>Please be cautious when modifying settings, as any incorrect changes can result in inaccessibility of stored files.';
