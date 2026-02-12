<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class tutorials_repository {

    public static function get_all(): array {
        global $DB;
        return $DB->get_records('edukav_tutorials', null, 'timecreated DESC');
    }

    public static function get_by_id(int $id) {
        global $DB;
        return $DB->get_record('edukav_tutorials', ['id' => $id], '*', MUST_EXIST);
    }

    public static function create(string $title, string $description, string $url): int {
        global $DB;

        $record = new \stdClass();
        $record->title = $title;
        $record->description = $description;
        $record->url = $url;
        $record->timecreated = time();
        $record->timemodified = time();

        return $DB->insert_record('edukav_tutorials', $record);
    }

    public static function update(\stdClass $record): bool {
        global $DB;

        debugging('ID recibido: ' . $record->id);

        return $DB->update_record('edukav_tutorials', $record);
    }

    public static function delete(int $id): bool {
        global $DB;
        return $DB->delete_records('edukav_tutorials', ['id' => $id]);
    }
}
