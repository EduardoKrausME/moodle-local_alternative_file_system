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
 * Migration status report.
 *
 * Shows totals, current rate based on timemodifield and an ETA.
 *
 * @package    local_alternative_file_system
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

require_once(__DIR__ . "/../../config.php");

require_login();
require_capability("moodle/site:config", context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_pagetype('admin-report');
$PAGE->set_url(new moodle_url('/local/alternative_file_system/report-migrate.php'));
$PAGE->set_title(get_string('reporttitle_status', 'local_alternative_file_system'));

echo $OUTPUT->header();

$cfg = get_config('local_alternative_file_system');

$storage = optional_param('storage', '', PARAM_ALPHANUMEXT);
$fast = optional_param('fast', 1, PARAM_INT) ? 1 : 0;

/**
 * Format seconds to a human string.
 *
 * @param int $seconds
 * @return string
 */
function local_alternative_file_system_format_seconds(int $seconds): string {
    if ($seconds <= 0) {
        return '-';
    }
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);

    if ($days > 0) {
        $hours = $hours % 24;
        return "{$days}d {$hours}h";
    }
    if ($hours > 0) {
        $minutes = $minutes % 60;
        return "{$hours}h {$minutes}m";
    }
    return "{$minutes}m";
}

/**
 * Format bytes.
 *
 * @param int|float $bytes
 * @return string
 */
function local_alternative_file_system_format_bytes($bytes): string {
    $bytes = (float) $bytes;
    if ($bytes <= 0) {
        return '-';
    }
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $i = 0;
    while ($bytes >= 1024 && $i < (count($units) - 1)) {
        $bytes /= 1024;
        $i++;
    }
    return sprintf('%.2f %s', $bytes, $units[$i]);
}

/**
 * Compute counts per window.
 *
 * @param string $storage
 * @param int $minutes
 * @return array{count:int, permin:float}
 */
function local_alternative_file_system_rate(string $storage, int $minutes): array {
    global $DB;

    $since = time() - ($minutes * 60);

    $sql = "SELECT COUNT(1)
              FROM {local_alternativefilesystemf}
             WHERE storage = :storage
               AND timemodifield >= :since";
    $count = $DB->count_records_sql($sql, ['storage' => $storage, 'since' => $since]);

    $permin = $minutes > 0 ? ($count / (float) $minutes) : 0.0;

    return ['count' => $count, 'permin' => $permin];
}

/**
 * Get a list of storages to report.
 *
 * @param string $selected
 * @param stdClass $cfg
 * @return string[]
 */
function local_alternative_file_system_get_storages(string $selected, stdClass $cfg): array {
    global $DB;

    $storages = [];

    // Prefer the selected storage.
    if ($selected !== '') {
        $storages[] = $selected;
        return $storages;
    }

    // Prefer configured destination.
    if (!empty($cfg->settings_destino) && $cfg->settings_destino !== 'local') {
        $storages[] = (string) $cfg->settings_destino;
    }

    // Add whatever exists in the log table.
    $sql = "SELECT DISTINCT storage
              FROM {local_alternativefilesystemf}
          ORDER BY storage ASC";
    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $r) {
        $s = (string) $r->storage;
        if ($s !== '' && !in_array($s, $storages, true)) {
            $storages[] = $s;
        }
    }
    $rs->close();

    return $storages;
}

/**
 * Compute bytes totals using a grouped contenthash -> filesize projection.
 * This may be heavy on large sites.
 *
 * @param string $storage
 * @return array{total:float, migrated:float, remaining:float, migratedlast10m:float, bytespermin10m:float}
 */
function local_alternative_file_system_bytes_stats(string $storage): array {
    global $DB;

    $since10m = time() - (10 * 60);

    // Distinct hash size projection (avoid duplicates in mdl_files).
    // NOTE: grouping can be heavy, hence "fast=1" exists.
    $filesprojection = "
        (SELECT contenthash, MIN(filesize) AS filesize
           FROM {files}
          WHERE filename <> '.'
            AND contenthash <> ''
          GROUP BY contenthash) fp
    ";

    $sqltotal = "SELECT COALESCE(SUM(fp.filesize), 0)
                   FROM {$filesprojection}";
    $total = (float) $DB->count_records_sql($sqltotal, []);

    $sqlmigrated = "SELECT COALESCE(SUM(fp.filesize), 0)
                      FROM {local_alternativefilesystemf} laf
                      JOIN {$filesprojection} ON fp.contenthash = laf.contenthash
                     WHERE laf.storage = :storage";
    $migrated = (float) $DB->count_records_sql($sqlmigrated, ['storage' => $storage]);

    $sqlmigrated10m = "SELECT COALESCE(SUM(fp.filesize), 0)
                         FROM {local_alternativefilesystemf} laf
                         JOIN {$filesprojection} ON fp.contenthash = laf.contenthash
                        WHERE laf.storage = :storage
                          AND laf.timemodifield >= :since";
    $migratedlast10m = (float) $DB->count_records_sql($sqlmigrated10m, ['storage' => $storage, 'since' => $since10m]);

    $bytespermin10m = $migratedlast10m / 10.0;

    return [
        'total' => $total, 'migrated' => $migrated, 'remaining' => max(0.0, $total - $migrated),
        'migratedlast10m' => $migratedlast10m, 'bytespermin10m' => $bytespermin10m,
    ];
}

$storages = local_alternative_file_system_get_storages($storage, $cfg);

if (empty($storages)) {
    echo $PAGE->get_renderer('core')->render(
        new notification(
            get_string('nostoragefound', 'local_alternative_file_system'), notification::NOTIFY_WARNING
        )
    );
    echo $OUTPUT->footer();
    exit;
}

// UI: storage selector.
$baseurl = new moodle_url('/local/alternative_file_system/report-migrate.php');
echo html_writer::start_div('mb-3');

foreach ($storages as $s) {
    $s = (string) $s;

    // Totals: distinct contenthash in mdl_files.
    $sqltotal = "
        SELECT COUNT(DISTINCT contenthash)
          FROM {files}
         WHERE filename LIKE '___%'
           AND filesize > 1";
    $totalhashes = $DB->count_records_sql($sqltotal, []);

    // Migrated: unique(contenthash, storage) so COUNT(*) is migrated hashes.
    $sqlmigrated = "
        SELECT COUNT(1)
          FROM {local_alternativefilesystemf}
         WHERE storage = :storage";
    $migrated = $DB->count_records_sql($sqlmigrated, ['storage' => $s]);

    $remaining = max(0, $totalhashes - $migrated);
    $percent = $totalhashes > 0 ? (100.0 * ($migrated / (float) $totalhashes)) : 0.0;

    // Last activity.
    $lasttime = $DB->get_field('local_alternativefilesystemf', 'MAX(timemodifield)', ['storage' => $s]) ?: 0;
    $laststr = $lasttime ? userdate($lasttime) : '-';

    // Rates.
    $r1 = local_alternative_file_system_rate($s, 1);
    $r10 = local_alternative_file_system_rate($s, 10);
    $r60 = local_alternative_file_system_rate($s, 60);

    // ETA by 10m rate (more stable than 1m).
    $etamin = null;
    if ($r10['permin'] > 0) {
        $etamin = (int) ceil($remaining / $r10['permin']);
    }
    $etaseconds = $etamin !== null ? $etamin * 60 : 0;

    // Optional bytes-based ETA (heavy).
    $bytesline = '';
    if (!$fast) {
        try {
            $bs = local_alternative_file_system_bytes_stats($s);

            $etabytesmin = null;
            if ($bs['bytespermin10m'] > 0) {
                $etabytesmin = (int) ceil($bs['remaining'] / $bs['bytespermin10m']);
            }
            $etabytesstr = $etabytesmin !== null ? local_alternative_file_system_format_seconds($etabytesmin * 60) : '-';

            $bytesline = html_writer::tag(
                'div', get_string(
                    'bytesline', 'local_alternative_file_system', [
                        'migrated' => local_alternative_file_system_format_bytes($bs['migrated']),
                        'total' => local_alternative_file_system_format_bytes($bs['total']),
                        'rate' => local_alternative_file_system_format_bytes($bs['bytespermin10m']), 'eta' => $etabytesstr,
                    ]
                ), ['class' => 'mt-2']
            );
        } catch (Throwable $e) {
            echo $PAGE->get_renderer('core')->render(
                new notification(
                    get_string('bytescalcfailed', 'local_alternative_file_system', s($e->getMessage())),
                    notification::NOTIFY_WARNING
                )
            );
        }
    }

    // Card UI.
    echo html_writer::start_div('card mb-3');
    echo html_writer::start_div('card-body');

    // Progress bar.
    $bar = html_writer::start_div('progress', ['style' => 'height: 18px;']);
    $bar .= html_writer::start_div('progress-bar', [
        'role' => 'progressbar',
        'style' => 'width: ' . min(100, max(0, $percent)) . '%;',
        'aria-valuenow' => (string) $percent,
        'aria-valuemin' => '0',
        'aria-valuemax' => '100',
    ]);
    $bar .= sprintf('%.2f%%', $percent);
    $bar .= html_writer::end_div();
    $bar .= html_writer::end_div();
    echo $bar;

    echo html_writer::start_div('mt-3');

    echo html_writer::tag(
        'div', get_string('totalfiles', 'local_alternative_file_system',
            number_format($totalhashes, 0, ',', '.'))
    );
    echo html_writer::tag(
        'div', get_string('migratedfiles', 'local_alternative_file_system',
            number_format($migrated, 0, ',', '.'))
    );
    echo html_writer::tag(
        'div', get_string('remainingfiles', 'local_alternative_file_system',
            number_format($remaining, 0, ',', '.'))
    );
    echo html_writer::tag(
        'div', get_string('lasttransfer', 'local_alternative_file_system', $laststr)
    );

    echo html_writer::tag(
        'div', get_string('rate', 'local_alternative_file_system', [
            'r1' => number_format($r1['permin'], 2, ',', '.'),
            'r10' => number_format($r10['permin'], 2, ',', '.'),
            'r60' => number_format($r60['permin'], 2, ',', '.'),
        ])
    );

    $etastr = $etamin !== null ? local_alternative_file_system_format_seconds($etaseconds) : '-';
    echo html_writer::tag(
        'div',
        get_string('eta', 'local_alternative_file_system', $etastr), ['class' => 'mt-2']);

    echo $bytesline;

    echo html_writer::end_div();

    echo html_writer::end_div(); // card-body.
    echo html_writer::end_div(); // card.
}

echo $OUTPUT->footer();
