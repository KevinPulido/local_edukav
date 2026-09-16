<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Seeds the default FAQ category on a fresh installation.
 */
function xmldb_local_edukav_install(): void {
    global $DB;

    $now = time();
    $DB->insert_record('edukav_faq_categories', (object) [
        'name' => get_string('category_general', 'local_edukav'),
        'slug' => 'general',
        'icon' => 'info-circle',
        'sortorder' => 0,
        'visible' => 1,
        'timecreated' => $now,
        'timemodified' => $now,
    ]);
}
