<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

/** Database access for course library resources. */
class course_library_repository {
    public static function get_by_course(int $courseid, bool $onlyvisible = true): array {
        global $DB;

        $conditions = ['courseid' => $courseid];
        if ($onlyvisible) {
            $conditions['visible'] = 1;
        }
        return array_values($DB->get_records('edukav_course_library', $conditions, 'sortorder ASC, title ASC'));
    }

    public static function get_by_id(int $id): ?\stdClass {
        global $DB;
        $record = $DB->get_record('edukav_course_library', ['id' => $id], '*', IGNORE_MISSING);
        return $record ?: null;
    }

    public static function create(\stdClass $record): int {
        global $DB;
        return (int)$DB->insert_record('edukav_course_library', $record);
    }

    public static function update(\stdClass $record): bool {
        global $DB;
        return $DB->update_record('edukav_course_library', $record);
    }

    public static function delete(int $id): bool {
        global $DB;
        return $DB->delete_records('edukav_course_library', ['id' => $id]);
    }

    public static function clear_featured(int $courseid, ?int $excludeid = null): void {
        global $DB;
        $params = ['courseid' => $courseid];
        $sql = 'courseid = :courseid';
        if ($excludeid !== null) {
            $sql .= ' AND id <> :excludeid';
            $params['excludeid'] = $excludeid;
        }
        $DB->set_field_select('edukav_course_library', 'featured', 0, $sql, $params);
    }

    public static function get_next_sortorder(int $courseid): int {
        global $DB;
        $maximum = $DB->get_field_sql(
            'SELECT MAX(sortorder) FROM {edukav_course_library} WHERE courseid = :courseid',
            ['courseid' => $courseid]
        );
        return ((int)$maximum) + 10;
    }

    public static function delete_by_course(int $courseid): void {
        global $DB;
        $DB->delete_records('edukav_course_library', ['courseid' => $courseid]);
    }
}
