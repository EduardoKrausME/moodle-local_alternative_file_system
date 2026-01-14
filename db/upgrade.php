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
 * @copyright  2024 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param int $oldversion
 *
 * @return bool
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_local_alternative_file_system_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024110501) {

        if (!$dbman->table_exists("local_alternativefilesystemf")) {
            $table = new xmldb_table("local_alternativefilesystemf");

            $table->add_field("id", XMLDB_TYPE_INTEGER, "10", true, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field("contenthash", XMLDB_TYPE_CHAR, "40", null, XMLDB_NOTNULL);
            $table->add_field("storage", XMLDB_TYPE_CHAR, "5", null, XMLDB_NOTNULL);
            $table->add_field("timemodifield", XMLDB_TYPE_INTEGER, "20", null, XMLDB_NOTNULL);

            $table->add_key("primary", XMLDB_KEY_PRIMARY, ["id"]);
            $table->add_index("contenthash", XMLDB_INDEX_NOTUNIQUE, ["contenthash", "storage"]);

            $dbman->create_table($table);
        }

        if ($dbman->table_exists("local_alternativefilesystemf")) {
            $sql = "INSERT INTO {local_alternativefilesystemf} (contenthash, storage, timemodifield)
                         SELECT contenthash, storage, timemodifield FROM {alternative_file_system_file}";
            $DB->execute($sql);

            $table = new xmldb_table("alternative_file_system_file");
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2024110501, "local", "alternative_file_system");
    }

    if ($oldversion < 2026011300) {
        $table = new xmldb_table('local_alternativefilesystemf');

        $index = new xmldb_index('contenthash', XMLDB_INDEX_NOTUNIQUE, ['contenthash']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('storage-contenthash', XMLDB_INDEX_NOTUNIQUE, ['storage', 'contenthash']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('storage-timemodifield', XMLDB_INDEX_NOTUNIQUE, ['storage', 'timemodifield']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2026011300, 'local', 'alternative_file_system');
    }

    if ($oldversion < 2026011400) {
        $table = new xmldb_table('local_alternativefilesystemf');

        $index = new xmldb_index('storage', XMLDB_INDEX_NOTUNIQUE, ['storage']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2026011400, 'local', 'alternative_file_system');
    }

    return true;
}
