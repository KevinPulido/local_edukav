<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class partners_repository {

    public static function get_all(bool $onlyvisible = false): array {
        global $DB;

        $conditions = $onlyvisible ? ['visible' => 1] : null;

        return $DB->get_records('edukav_partners', $conditions, 'name ASC');
    }

    public static function get_by_id(int $id): ?\stdClass {
        global $DB;

        $record = $DB->get_record('edukav_partners', ['id' => $id], '*', IGNORE_MISSING);
        return $record ?: null;
    }

    public static function get_by_slug(string $slug): ?\stdClass {
        global $DB;

        $record = $DB->get_record('edukav_partners', ['slug' => $slug], '*', IGNORE_MISSING);
        return $record ?: null;
    }

    public static function create(\stdClass $record): int {
        global $DB;

        return $DB->insert_record('edukav_partners', $record);
    }

    public static function update(\stdClass $record): bool {
        global $DB;

        return $DB->update_record('edukav_partners', $record);
    }

    public static function delete(int $id): bool {
        global $DB;

        return $DB->delete_records('edukav_partners', ['id' => $id]);
    }
}
