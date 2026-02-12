<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class faqs_users_repository {

    public static function get_all(): array {
        global $DB;
        return $DB->get_records('edukav_faqs_users', null, 'timecreated DESC');
    }

    public static function get_by_id(int $id) {
        global $DB;
        return $DB->get_record('edukav_faqs_users', ['id' => $id], '*', MUST_EXIST);
    }

    public static function create(string $name, string $email,string $question): int {
        global $DB;

        $record = new \stdClass();
        $record->name = $name;
        $record->email = $email;
        $record->question = $question;
        $record->timecreated = time();
        $record->timemodified = time();

        return $DB->insert_record('edukav_faqs_users', $record);
    }

    public static function update(\stdClass $record): bool {
        global $DB;

        debugging('ID recibido: ' . $record->id);

        return $DB->update_record('edukav_faqs_users', $record);
    }

    public static function delete(int $id): bool {
        global $DB;
        return $DB->delete_records('edukav_faqs_users', ['id' => $id]);
    }
}
