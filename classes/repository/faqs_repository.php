<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class faqs_repository {
    public static function get_all(): array {
        global $DB;

        $sql = "SELECT f.id,
                       f.categoryid,
                       f.question,
                       f.answer,
                       f.visible,
                       f.sortorder,
                       f.timecreated,
                       f.timemodified,
                       c.name AS categoryname,
                       c.icon AS categoryicon
                  FROM {edukav_faqs} f
                  JOIN {edukav_faq_categories} c
                    ON c.id = f.categoryid
              ORDER BY c.sortorder ASC, c.name ASC, f.sortorder ASC, f.id ASC";

        return $DB->get_records_sql($sql);
    }

    public static function get_visible(): array {
        global $DB;

        $sql = "SELECT f.id,
                       f.categoryid,
                       f.question,
                       f.answer,
                       f.visible,
                       f.sortorder,
                       f.timecreated,
                       f.timemodified,
                       c.name AS categoryname,
                       c.icon AS categoryicon
                  FROM {edukav_faqs} f
                  JOIN {edukav_faq_categories} c
                    ON c.id = f.categoryid
                 WHERE f.visible = :faqvisible
                       AND c.visible = :categoryvisible
              ORDER BY c.sortorder ASC, c.name ASC, f.sortorder ASC, f.id ASC";
        $params = [
            'faqvisible' => 1,
            'categoryvisible' => 1,
        ];

        return $DB->get_records_sql($sql, $params);
    }

    public static function get_by_id(int $id): \stdClass {
        global $DB;

        $fields = 'id, categoryid, question, answer, visible, sortorder, timecreated, timemodified';
        return $DB->get_record('edukav_faqs', ['id' => $id], $fields, MUST_EXIST);
    }

    public static function save(\stdClass $record): int {
        global $DB;
        $record->timemodified = time();
        if (!empty($record->id)) {
            $DB->update_record('edukav_faqs', $record);
            return (int) $record->id;
        }
        unset($record->id);
        $record->timecreated = $record->timemodified;
        return (int) $DB->insert_record('edukav_faqs', $record);
    }

    public static function delete(int $id): bool {
        global $DB;
        return $DB->delete_records('edukav_faqs', ['id' => $id]);
    }
}
