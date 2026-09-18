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
     * @param string $level
     * @param string $search
     * @return int[]
     */
    public static function get_visible_course_ids(
        array $selectedids = [],
        int $limit = 3,
        int $offset = 0,
        int $categoryid = 0,
        string $level = '',
        string $search = ''
    ): array {
        global $DB;

        [$joins, $where, $params] = self::get_catalogue_filter_sql($categoryid, $level, $search);

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
                  {$joins}
                 WHERE " . implode(' AND ', $where) . "
              ORDER BY {$order}";

        return array_map('intval', array_keys($DB->get_records_sql($sql, $params, $offset, $limit)));
    }

    /**
     * Count visible courses, optionally within a category.
     *
     * @param int $categoryid
     * @param string $level
     * @param string $search
     * @return int
     */
    public static function count_visible_courses(
        int $categoryid = 0,
        string $level = '',
        string $search = ''
    ): int {
        global $DB;

        [$joins, $where, $params] = self::get_catalogue_filter_sql($categoryid, $level, $search);

        return (int)$DB->count_records_sql(
            "SELECT COUNT(c.id)
               FROM {course} c
               {$joins}
              WHERE " . implode(' AND ', $where),
            $params
        );
    }

    /**
     * Return the number of visible catalogue courses for each supported level.
     *
     * The level filter itself is intentionally omitted so every option keeps its
     * complete count while category and search filters remain applied.
     *
     * @param int $categoryid
     * @param string $search
     * @return array<string, int>
     */
    public static function get_visible_course_level_counts(int $categoryid = 0, string $search = ''): array {
        global $DB;

        [$joins, $where, $params] = self::get_catalogue_filter_sql($categoryid, '', $search);
        $joins .= " JOIN {course_format_options} cfolevelcount
                         ON cfolevelcount.courseid = c.id
                        AND cfolevelcount.format = :countformat
                        AND cfolevelcount.sectionid = 0
                        AND cfolevelcount.name = :countname";
        $params['countformat'] = 'edukav';
        $params['countname'] = 'level';
        [$levelsql, $levelparams] = $DB->get_in_or_equal(
            ['beginner', 'intermediate', 'advanced'],
            SQL_PARAMS_NAMED,
            'countlevel'
        );
        $params += $levelparams;
        $where[] = "cfolevelcount.value {$levelsql}";

        $sql = "SELECT cfolevelcount.value AS level, COUNT(DISTINCT c.id) AS coursecount
                  FROM {course} c
                  {$joins}
                 WHERE " . implode(' AND ', $where) . "
              GROUP BY cfolevelcount.value";

        $counts = [];
        foreach ($DB->get_records_sql($sql, $params) as $record) {
            $counts[$record->level] = (int)$record->coursecount;
        }
        return $counts;
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

        $sql = "SELECT cc.id,
                       cc.name,
                       cc.description,
                       cc.descriptionformat,
                       cs.id AS styleid,
                       cs.displaytype,
                       cs.icontype,
                       cs.icon,
                       cs.imagealt,
                       COUNT(c.id) AS coursecount
                  FROM {course_categories} cc
                  JOIN {course} c ON c.category = cc.id
             LEFT JOIN {edukav_category_styles} cs ON cs.categoryid = cc.id
                 WHERE " . implode(' AND ', $where) . "
              GROUP BY cc.id,
                       cc.name,
                       cc.description,
                       cc.descriptionformat,
                       cc.sortorder,
                       cs.id,
                       cs.displaytype,
                       cs.icontype,
                       cs.icon,
                       cs.imagealt
              ORDER BY {$order}";

        return array_values($DB->get_records_sql($sql, $params, 0, $limit));
    }

    /**
     * Build portable SQL fragments shared by catalogue queries.
     *
     * @param int $categoryid
     * @param string $level
     * @param string $search
     * @return array{0:string,1:string[],2:array}
     */
    private static function get_catalogue_filter_sql(int $categoryid, string $level, string $search): array {
        global $DB;

        $joins = 'JOIN {course_categories} cc ON cc.id = c.category';
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

        if ($level !== '') {
            $joins .= " JOIN {course_format_options} cfolevel
                             ON cfolevel.courseid = c.id
                            AND cfolevel.format = :levelformat
                            AND cfolevel.sectionid = 0
                            AND cfolevel.name = :leveloption";
            $where[] = 'cfolevel.value = :level';
            $params['levelformat'] = 'edukav';
            $params['leveloption'] = 'level';
            $params['level'] = $level;
        }

        $search = trim($search);
        if ($search !== '') {
            $where[] = '(' . $DB->sql_like('c.fullname', ':searchfullname', false) .
                ' OR ' . $DB->sql_like('c.shortname', ':searchshortname', false) . ')';
            $searchvalue = '%' . $DB->sql_like_escape($search) . '%';
            $params['searchfullname'] = $searchvalue;
            $params['searchshortname'] = $searchvalue;
        }

        return [$joins, $where, $params];
    }
}
