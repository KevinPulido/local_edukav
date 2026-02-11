<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class faqs_repository {

    public static function get_all(): array {
        global $DB;
        return $DB->get_records('edukav_faqs', null, 'timecreated DESC');
    }

    public static function get_by_id(int $id) {
        global $DB;
        return $DB->get_record('edukav_faqs', ['id' => $id], '*', MUST_EXIST);
    }

    public static function create(string $question, string $answer): int {
        global $DB;

        $record = new \stdClass();
        $record->question = $question;
        $record->answer = $answer;
        $record->timecreated = time();
        $record->timemodified = time();

        return $DB->insert_record('edukav_faqs', $record);
    }

    public static function update(\stdClass $record): bool {
        global $DB;

        debugging('ID recibido: ' . $record->id);

        return $DB->update_record('edukav_faqs', $record);
    }

    public static function delete(int $id): bool {
        global $DB;
        return $DB->delete_records('edukav_faqs', ['id' => $id]);
    }
}
