<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class tutorials_repository {

    public static function get_all(): array {
        global $DB;
        $fields = 'id, title, description, url, timecreated, timemodified';
        return $DB->get_records('edukav_tutorials', null, 'timecreated DESC, id DESC', $fields);
    }

    public static function get_by_id(int $id) {
        global $DB;
        return $DB->get_record('edukav_tutorials', ['id' => $id], '*', MUST_EXIST);
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
