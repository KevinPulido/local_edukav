<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class faq_categories_repository {
    public static function get_all(): array {
        global $DB;

        $sql = "SELECT c.id,
                       c.name,
                       c.slug,
                       c.icon,
                       c.sortorder,
                       c.visible,
                       c.timecreated,
                       c.timemodified,
                       COUNT(f.id) AS faqcount
                  FROM {edukav_faq_categories} c
             LEFT JOIN {edukav_faqs} f
                    ON f.categoryid = c.id
              GROUP BY c.id, c.name, c.slug, c.icon, c.sortorder, c.visible, c.timecreated, c.timemodified
              ORDER BY c.sortorder ASC, c.name ASC";

        return $DB->get_records_sql($sql);
    }

    public static function get_visible(): array {
        global $DB;

        $sql = "SELECT c.id,
                       c.name,
                       c.slug,
                       c.icon,
                       c.sortorder,
                       c.visible,
                       c.timecreated,
                       c.timemodified,
                       COUNT(f.id) AS faqcount
                  FROM {edukav_faq_categories} c
             LEFT JOIN {edukav_faqs} f
                    ON f.categoryid = c.id
                       AND f.visible = :faqvisible
                 WHERE c.visible = :categoryvisible
              GROUP BY c.id, c.name, c.slug, c.icon, c.sortorder, c.visible, c.timecreated, c.timemodified
                HAVING COUNT(f.id) > 0
              ORDER BY c.sortorder ASC, c.name ASC";
        $params = [
            'faqvisible' => 1,
            'categoryvisible' => 1,
        ];

        return $DB->get_records_sql($sql, $params);
    }

    public static function get_options(): array {
        global $DB;

        return $DB->get_records_menu(
            'edukav_faq_categories',
            null,
            'sortorder ASC, name ASC',
            'id, name'
        );
    }

    public static function get_by_id(int $id): \stdClass {
        global $DB;

        $fields = 'id, name, slug, icon, sortorder, visible, timecreated, timemodified';
        return $DB->get_record('edukav_faq_categories', ['id' => $id], $fields, MUST_EXIST);
    }

    public static function slug_exists(string $slug, int $excludeid = 0): bool {
        global $DB;
        if ($excludeid) {
            return $DB->record_exists_select('edukav_faq_categories', 'slug = :slug AND id <> :id',
                ['slug' => $slug, 'id' => $excludeid]);
        }
        return $DB->record_exists('edukav_faq_categories', ['slug' => $slug]);
    }

    public static function save(\stdClass $record): int {
        global $DB;
        $record->timemodified = time();
        if (!empty($record->id)) {
            $DB->update_record('edukav_faq_categories', $record);
            return (int) $record->id;
        }
        unset($record->id);
        $record->timecreated = $record->timemodified;
        return (int) $DB->insert_record('edukav_faq_categories', $record);
    }

    public static function has_faqs(int $id): bool {
        global $DB;
        return $DB->record_exists('edukav_faqs', ['categoryid' => $id]);
    }

    public static function delete(int $id): bool {
        global $DB;
        return $DB->delete_records('edukav_faq_categories', ['id' => $id]);
    }
}
