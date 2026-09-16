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

    if ($oldversion < 2026091500) {
        $table = new xmldb_table('edukav_course_library');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
            $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null);
            $table->add_field('category', XMLDB_TYPE_CHAR, '100', null, null);
            $table->add_field('resourcetype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'document');
            $table->add_field('level', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'basic');
            $table->add_field('externalurl', XMLDB_TYPE_TEXT, null, null, null);
            $table->add_field('featured', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('courseid_fk', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
            $table->add_index('coursevisible_idx', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'visible']);
            $table->add_index('coursetype_idx', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'resourcetype']);
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026091500, 'local', 'edukav');
    }

    if ($oldversion < 2026091501) {
        $legacytable = new xmldb_table('edukav_library_resource');
        if ($dbman->table_exists($legacytable)) {
            $sortorders = [];
            foreach ($DB->get_records('edukav_library_resource', null, 'courseid ASC, id ASC') as $legacy) {
                $courseid = (int)$legacy->courseid;
                if (!$DB->record_exists('course', ['id' => $courseid])) {
                    continue;
                }
                $sortorders[$courseid] = ($sortorders[$courseid] ?? 0) + 10;
                $externalurl = clean_param((string)($legacy->url ?? ''), PARAM_URL);
                $duplicate = $DB->record_exists('edukav_course_library', [
                    'courseid' => $courseid,
                    'title' => (string)$legacy->title,
                    'externalurl' => $externalurl,
                ]);
                if ($duplicate) {
                    continue;
                }
                $DB->insert_record('edukav_course_library', (object)[
                    'courseid' => $courseid,
                    'title' => (string)$legacy->title,
                    'description' => (string)($legacy->description ?? ''),
                    'category' => (string)($legacy->category ?? ''),
                    'resourcetype' => (string)($legacy->resourcetype ?? 'document'),
                    'level' => (string)($legacy->level ?? 'basic'),
                    'externalurl' => $externalurl,
                    'featured' => empty($legacy->featured) ? 0 : 1,
                    'visible' => empty($legacy->visible) ? 0 : 1,
                    'sortorder' => $sortorders[$courseid],
                    'createdby' => (int)($legacy->createdby ?? 0),
                    'modifiedby' => (int)($legacy->createdby ?? 0),
                    'timecreated' => (int)($legacy->timecreated ?? time()),
                    'timemodified' => (int)($legacy->timemodified ?? time()),
                ]);
            }
            $dbman->drop_table($legacytable);
        }
        upgrade_plugin_savepoint(true, 2026091501, 'local', 'edukav');
    }

    if ($oldversion < 2026091511) {
        $categorytable = new xmldb_table('edukav_faq_categories');
        if (!$dbman->table_exists($categorytable)) {
            $categorytable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $categorytable->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
            $categorytable->add_field('slug', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL);
            $categorytable->add_field('icon', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, 'help-circle');
            $categorytable->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $categorytable->add_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $categorytable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $categorytable->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $categorytable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $categorytable->add_key('slug_uix', XMLDB_KEY_UNIQUE, ['slug']);
            $dbman->create_table($categorytable);
        }

        $general = $DB->get_record('edukav_faq_categories', ['slug' => 'general']);
        if (!$general) {
            $now = time();
            $generalid = $DB->insert_record('edukav_faq_categories', (object) [
                'name' => get_string('category_general', 'local_edukav'),
                'slug' => 'general',
                'icon' => 'info',
                'sortorder' => 0,
                'visible' => 1,
                'timecreated' => $now,
                'timemodified' => $now,
            ]);
        } else {
            $generalid = (int) $general->id;
        }

        $faqtable = new xmldb_table('edukav_faqs');
        $categoryfield = new xmldb_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');
        if (!$dbman->field_exists($faqtable, $categoryfield)) {
            $dbman->add_field($faqtable, $categoryfield);
        }
        $DB->execute('UPDATE {edukav_faqs} SET categoryid = :categoryid WHERE categoryid IS NULL OR categoryid = 0',
            ['categoryid' => $generalid]);
        $categoryfield = new xmldb_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $dbman->change_field_notnull($faqtable, $categoryfield);

        $sortfield = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'visible');
        if (!$dbman->field_exists($faqtable, $sortfield)) {
            $dbman->add_field($faqtable, $sortfield);
        }

        $categorykey = new xmldb_key('categoryid_fk', XMLDB_KEY_FOREIGN, ['categoryid'],
            'edukav_faq_categories', ['id']);
        if (!$dbman->find_key_name($faqtable, $categorykey)) {
            $dbman->add_key($faqtable, $categorykey);
        }
        $categoryindex = new xmldb_index('categoryvisible_idx', XMLDB_INDEX_NOTUNIQUE,
            ['categoryid', 'visible', 'sortorder']);
        if (!$dbman->index_exists($faqtable, $categoryindex)) {
            $dbman->add_index($faqtable, $categoryindex);
        }

        upgrade_plugin_savepoint(true, 2026091511, 'local', 'edukav');
    }

    if ($oldversion < 2026091512) {
        $faqtable = new xmldb_table('edukav_faqs');
        $categoryindex = new xmldb_index('categoryid_fk', XMLDB_INDEX_NOTUNIQUE, ['categoryid']);
        if (!$dbman->index_exists($faqtable, $categoryindex)) {
            $dbman->add_index($faqtable, $categoryindex);
        }
        upgrade_plugin_savepoint(true, 2026091512, 'local', 'edukav');
    }

    if ($oldversion < 2026091513) {
        $categorytable = new xmldb_table('edukav_faq_categories');
        $iconfield = new xmldb_field('icon', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null,
            'question-circle', 'slug');
        $dbman->change_field_default($categorytable, $iconfield);

        $iconmap = [
            'user' => 'person',
            'book-open' => 'book',
            'file-text' => 'file-earmark-text',
            'headphones' => 'headset',
            'info' => 'info-circle',
            'help-circle' => 'question-circle',
        ];
        foreach ($iconmap as $oldicon => $newicon) {
            $DB->set_field('edukav_faq_categories', 'icon', $newicon, ['icon' => $oldicon]);
        }

        upgrade_plugin_savepoint(true, 2026091513, 'local', 'edukav');
    }

    return true;
}
