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
 * upgrade file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param int $oldversion
 *
 * @return bool
 *
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_local_alternative_file_system_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024020501) {
        $table = new xmldb_table('local_alternative_file_system_usage');
        $field = new xmldb_field('model', XMLDB_TYPE_CHAR, '40', null, true, null, null, 'receive');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $model = get_config('local_alternative_file_system', 'model');
        $sql = "UPDATE {local_alternative_file_system_usage} SET model = '{$model}'";
        $DB->execute($sql);

        upgrade_plugin_savepoint(true, 2024020501, 'local', 'alternative_file_system');
    }

    if ($oldversion < 2024040500) {
        $table = new xmldb_table('local_alternative_file_system_usage');
        $field = new xmldb_field('datecreated', XMLDB_TYPE_CHAR, '10', null, true, null, null, 'timecreated');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $usages = $DB->get_records('local_alternative_file_system_usage');
        foreach ($usages as $usage) {
            $usage->datecreated = date("Y-m-d", $usage->timecreated);
            $DB->update_record("local_alternative_file_system_usage", $usage);
        }

        upgrade_plugin_savepoint(true, 2024040500, 'local', 'alternative_file_system');
    }

    return true;
}
