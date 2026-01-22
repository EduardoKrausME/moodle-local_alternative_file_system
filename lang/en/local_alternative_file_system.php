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
 * phpcs:disable moodle.Files.LangFilesOrdering.IncorrectOrder
 *
 * Lang en file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['instruction_install'] = '<p><strong>Add the line below to the Moodle <code>config.php</code> file:</strong></p><pre><code>$CFG->alternative_file_system_class = \'\local_alternative_file_system\external_file_system\';</code></pre><p><strong>Important:</strong> Insert this line <strong>before</strong> the following line, if it exists in your file:</p><pre><code>require_once(__DIR__ . \'/lib/setup.php\');</code></pre>';
$string['instruction_title'] = 'Installation Instructions';
$string['migrate_link'] = '<p><a class="btn btn-success" href="?execute=1">Execute Now (may take a long time)</a></p>';
$string['migrate_title'] = 'Migrate local storage to remote storage';
$string['migrate_title_tolocal'] = 'Migrate remote storage to local storage';
$string['migrate_total'] = '<p>You have <strong>{$a->missing}</strong> local files awaiting migration, while <strong>{$a->sending}</strong> files have already been migrated to the remote environment.</p>';
$string['migrate_total_local'] = 'Moves files from remote storage back to local storage. This may take some time depending on the amount of data.';
$string['pluginname'] = 'Alternative File System';
$string['privacy:no_data_reason'] = 'The Alternative File System plugin does not store any personal data.';
$string['settings_bucketname'] = '{$a->local} Bucket Name';
$string['settings_bucketnamedesc'] = 'The unique name assigned to the bucket in {$a->local}.';
$string['settings_destino'] = 'Storage Destination';
$string['settings_destinodesc'] = 'Choose the storage destination and save to load storage-related data.';
$string['settings_gcs_keyfile'] = 'Google-storage.json Content';
$string['settings_gcs_keyfiledesc'] = 'Paste here the content of the "google-storage.json" file.';
$string['settings_local'] = 'Local files in Moodle';
$string['settings_migrate_local']  = 'Use the <a target="_blank" href="{$a->url}/move-to-local.php">move-to-local.php</a> service to migrate data from {$a->local} to local storage.';
$string['settings_migrate_remote'] = 'Use the <a target="_blank" href="{$a->url}/move-to-external.php">move-to-external.php</a> service to migrate data from local storage to {$a->local}.';
$string['settings_path'] = '{$a->local} Object Path';
$string['settings_pathdesc'] = 'The path within the bucket where objects will be stored. Only letters and numbers are accepted.';
$string['settings_s3_credentials_key'] = '{$a->local} Access Key';
$string['settings_s3_credentials_keydesc'] = 'The access key used to authenticate with the {$a->local} service.';
$string['settings_s3_credentials_secret'] = '{$a->local} Secret Key';
$string['settings_s3_credentials_secretdesc'] = 'The secret key used to authenticate with the {$a->local} service.';
$string['settings_s3_region'] = '{$a->local} Region';
$string['settings_s3_regiondesc'] = 'The region where the {$a->local} bucket is located, for example, "{$a->ex_region}".';
$string['settings_success'] = '<h2>Data is correct.</h2>Please be cautious when modifying settings, as any incorrect changes can result in inaccessibility of stored files.';
$string['reporttitle_status'] = 'Status';
$string['nostoragefound'] = 'No storage found to report. Check if the table local_alternativefilesystemf has records.';
$string['totalfiles'] = 'Total files in MDL_FILES: <strong>{$a}</strong>';
$string['migratedfiles'] = 'Migrated: <strong>{$a}</strong>';
$string['remainingfiles'] = 'Remaining: <strong>{$a}</strong>';
$string['lasttransfer'] = 'Last transfer: <strong>{$a}</strong>';
$string['rate'] = 'Rate: <strong>{$a->r1}</strong> file/min (1m) &nbsp;|&nbsp; <strong>{$a->r10}</strong> file/min (10m) &nbsp;|&nbsp; <strong>{$a->r60}</strong> file/min (60m)';
$string['eta'] = 'ETA: <strong>{$a}</strong>';
$string['bytesline'] = 'Bytes: <strong>{$a->migrated}</strong> / <strong>{$a->total}</strong> &nbsp;|&nbsp; Rate (10m): <strong>{$a->rate}/min</strong> &nbsp;|&nbsp; ETA (bytes): <strong>{$a->eta}</strong>';
$string['bytescalcfailed'] = 'Failed to calculate bytes (fast=0 mode). Reason: {$a}';
$string['settings_objectfs_notice'] = '
<p><strong>Current Alternative File System:</strong> <code>{$a->currentclass}</code></p>
<p>This site is currently using <strong>tool_objectfs</strong> as the Moodle alternative file system. While this is enabled, the scheduled task
<strong>"Migrate objects from tool_objectfs to Alternative File System"</strong> will copy each object from the current DigitalOcean Spaces
(ObjectFS) to the destination configured in this plugin (S3/Spaces/GCS).</p>
<p><strong>How the transfer works</strong></p>
<ul>
  <li>Moodle continues reading/writing using <strong>tool_objectfs</strong>.</li>
  <li>CRON copies the objects in the background to the destination configured in <strong>local_alternative_file_system</strong>.</li>
  <li>You can monitor progress in {$a->reportlink}.</li>
</ul>
<p><strong>When the report shows that the migration is finished</strong> (no missing items / 100% completed), update your Moodle <code>config.php</code> and switch the alternative file system class to:</p>
<pre><code>$CFG->alternative_file_system_class = \'\\local_alternative_file_system\\external_file_system\';</code></pre>
<p><strong>Important:</strong> after switching, keep monitoring {$a->reportlink} for a while to ensure there is no new backlog.</p>';
