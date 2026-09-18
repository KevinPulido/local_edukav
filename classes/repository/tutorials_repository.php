<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class tutorials_repository {

    public static function get_all(): array {
        global $DB;

        $sql = "SELECT t.id,
                       t.categoryid,
                       t.title,
                       t.description,
                       t.url,
                       t.visible,
                       t.sortorder,
                       t.timecreated,
                       t.timemodified,
                       c.name AS categoryname,
                       c.icon AS categoryicon
                  FROM {edukav_tutorials} t
                  JOIN {edukav_tutorial_categories} c
                    ON c.id = t.categoryid
              ORDER BY c.sortorder ASC, c.name ASC, t.sortorder ASC, t.id ASC";

        return $DB->get_records_sql($sql);
    }

    public static function get_visible(): array {
        global $DB;

        $sql = "SELECT t.id,
                       t.categoryid,
                       t.title,
                       t.description,
                       t.url,
                       t.visible,
                       t.sortorder,
                       t.timecreated,
                       t.timemodified,
                       c.name AS categoryname,
                       c.icon AS categoryicon
                  FROM {edukav_tutorials} t
                  JOIN {edukav_tutorial_categories} c
                    ON c.id = t.categoryid
                 WHERE t.visible = :tutorialvisible
                       AND c.visible = :categoryvisible
              ORDER BY c.sortorder ASC, c.name ASC, t.sortorder ASC, t.id ASC";
        $params = [
            'tutorialvisible' => 1,
            'categoryvisible' => 1,
        ];

        return $DB->get_records_sql($sql, $params);
    }

    public static function get_by_id(int $id) {
        global $DB;
        $fields = 'id, categoryid, title, description, url, visible, sortorder, timecreated, timemodified';
        return $DB->get_record('edukav_tutorials', ['id' => $id], $fields, MUST_EXIST);
    }

    public static function save(\stdClass $record): int {
        global $DB;
        $record->timemodified = time();
        if (!empty($record->id)) {
            $DB->update_record('edukav_tutorials', $record);
            return (int) $record->id;
        }
        unset($record->id);
        $record->timecreated = $record->timemodified;
        return (int) $DB->insert_record('edukav_tutorials', $record);
    }

    public static function delete(int $id): bool {
        global $DB;
        return $DB->delete_records('edukav_tutorials', ['id' => $id]);
    }
}
