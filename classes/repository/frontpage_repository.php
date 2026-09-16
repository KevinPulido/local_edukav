<?php
// This file is part of Moodle - http://moodle.org/

namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

/**
 * DML queries used by the Edukav public frontpage and catalogue.
 *
 * @package    local_edukav
 * @copyright  2026 Edukav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontpage_repository {

    /**
     * Return the public aggregate statistics.
     *
     * @return array{courses:int,learners:int,categories:int}
     */
    public static function get_statistics(): array {
        global $DB;

        $now = time();
        $visiblecoursesql = "FROM {course} c
                             JOIN {course_categories} cc ON cc.id = c.category
                            WHERE c.id <> :siteid
                              AND c.visible = 1
                              AND cc.visible = 1";

        $courses = $DB->count_records_sql(
            "SELECT COUNT(c.id) {$visiblecoursesql}",
            ['siteid' => SITEID]
        );

        $categories = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT cc.id) {$visiblecoursesql}",
            ['siteid' => SITEID]
        );

        $learnersql = "SELECT COUNT(DISTINCT ue.userid)
                         FROM {user_enrolments} ue
                         JOIN {enrol} e ON e.id = ue.enrolid
                         JOIN {course} c ON c.id = e.courseid
                         JOIN {course_categories} cc ON cc.id = c.category
                         JOIN {user} u ON u.id = ue.userid
                        WHERE c.id <> :siteid
                          AND c.visible = 1
                          AND cc.visible = 1
                          AND e.status = 0
                          AND ue.status = 0
                          AND (ue.timestart = 0 OR ue.timestart <= :nowstart)
                          AND (ue.timeend = 0 OR ue.timeend > :nowend)
                          AND u.deleted = 0
                          AND u.suspended = 0";
        $learners = $DB->count_records_sql($learnersql, [
            'siteid' => SITEID,
            'nowstart' => $now,
            'nowend' => $now,
        ]);

        return [
            'courses' => (int)$courses,
            'learners' => (int)$learners,
            'categories' => (int)$categories,
        ];
    }

    /**
     * Return visible course ids, preserving an explicit selection first.
     *
     * @param int[] $selectedids
     * @param int $limit
     * @param int $offset
     * @param int $categoryid
     * @return int[]
     */
    public static function get_visible_course_ids(
        array $selectedids = [],
        int $limit = 6,
        int $offset = 0,
        int $categoryid = 0
    ): array {
        global $DB;

        $params = ['siteid' => SITEID];
        $where = [
            'c.id <> :siteid',
            'c.visible = 1',
            'cc.visible = 1',
        ];
        if ($categoryid > 0) {
            $where[] = 'c.category = :categoryid';
            $params['categoryid'] = $categoryid;
        }

        $order = 'c.sortorder ASC, c.id ASC';
        if ($selectedids) {
            [$insql, $inparams] = $DB->get_in_or_equal($selectedids, SQL_PARAMS_NAMED, 'selected');
            $where[] = "c.id {$insql}";
            $params += $inparams;
            $cases = [];
            foreach (array_values($selectedids) as $position => $courseid) {
                $key = 'orderid' . $position;
                $cases[] = "WHEN :{$key} THEN {$position}";
                $params[$key] = $courseid;
            }
            $order = 'CASE c.id ' . implode(' ', $cases) . ' ELSE ' . count($selectedids) . ' END, c.sortorder ASC';
        }

        $sql = "SELECT c.id
                  FROM {course} c
                  JOIN {course_categories} cc ON cc.id = c.category
                 WHERE " . implode(' AND ', $where) . "
              ORDER BY {$order}";

        return array_map('intval', array_keys($DB->get_records_sql($sql, $params, $offset, $limit)));
    }

    /**
     * Count visible courses, optionally within a category.
     *
     * @param int $categoryid
     * @return int
     */
    public static function count_visible_courses(int $categoryid = 0): int {
        global $DB;

        $params = ['siteid' => SITEID];
        $categorysql = '';
        if ($categoryid > 0) {
            $categorysql = ' AND c.category = :categoryid';
            $params['categoryid'] = $categoryid;
        }

        return (int)$DB->count_records_sql(
            "SELECT COUNT(c.id)
               FROM {course} c
               JOIN {course_categories} cc ON cc.id = c.category
              WHERE c.id <> :siteid
                AND c.visible = 1
                AND cc.visible = 1{$categorysql}",
            $params
        );
    }

    /**
     * Return visible categories that contain at least one visible course.
     *
     * @param int[] $selectedids
     * @param int $limit Zero means no limit.
     * @return array
     */
    public static function get_visible_categories(array $selectedids = [], int $limit = 0): array {
        global $DB;

        $params = ['siteid' => SITEID];
        $where = ['cc.visible = 1', 'c.id <> :siteid', 'c.visible = 1'];
        $order = 'cc.sortorder ASC, cc.name ASC';

        if ($selectedids) {
            [$insql, $inparams] = $DB->get_in_or_equal($selectedids, SQL_PARAMS_NAMED, 'category');
            $where[] = "cc.id {$insql}";
            $params += $inparams;
            $cases = [];
            foreach (array_values($selectedids) as $position => $categoryid) {
                $key = 'categoryorder' . $position;
                $cases[] = "WHEN :{$key} THEN {$position}";
                $params[$key] = $categoryid;
            }
            $order = 'CASE cc.id ' . implode(' ', $cases) . ' ELSE ' . count($selectedids) . ' END, cc.sortorder ASC';
        }

        $sql = "SELECT cc.id, cc.name, cc.description, cc.descriptionformat, COUNT(c.id) AS coursecount
                  FROM {course_categories} cc
                  JOIN {course} c ON c.category = cc.id
                 WHERE " . implode(' AND ', $where) . "
              GROUP BY cc.id, cc.name, cc.description, cc.descriptionformat, cc.sortorder
              ORDER BY {$order}";

        return array_values($DB->get_records_sql($sql, $params, 0, $limit));
    }
}
