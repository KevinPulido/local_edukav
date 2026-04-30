<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade steps for local_edukav.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_edukav_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026021008) {
        $table = new xmldb_table('edukav_partners');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('slug', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('logo', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('brand_color', XMLDB_TYPE_CHAR, '7', null, null, null, null);
            $table->add_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('slug_uix', XMLDB_KEY_UNIQUE, ['slug']);

            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026021009, 'local', 'edukav');
    }

    return true;
}
