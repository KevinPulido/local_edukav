<?php
// This file is part of Moodle - http://moodle.org/

namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use cache;
use context_coursecat;
use core_course_category;
use local_edukav\repository\frontpage_repository;
use moodle_url;

/**
 * Prepares presentation-neutral data for the Edukav frontpage and catalogue.
 *
 * @package    local_edukav
 * @copyright  2026 Edukav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontpage_service {

    /** Maximum number of featured courses accepted by the frontpage. */
    public const FEATURED_COURSE_LIMIT = 6;

    /** Course levels supported by the Edukav course format. */
    private const CATALOGUE_LEVELS = ['beginner', 'intermediate', 'advanced'];

    /**
     * Return cached public statistics.
     *
     * @return array{courses:int,learners:int,categories:int}
     */
    public static function get_statistics(): array {
        $cache = cache::make('local_edukav', 'frontpage_stats');
        $statistics = $cache->get('public');
        if ($statistics === false) {
            $statistics = frontpage_repository::get_statistics();
            $cache->set('public', $statistics);
        }
        return $statistics;
    }

    /**
     * Build all dynamic data consumed by the frontpage template.
     *
     * @param int[] $featuredcourseids
     * @param int[] $categoryids
     * @return array
     */
    public static function get_frontpage_data(array $featuredcourseids = [], array $categoryids = []): array {
        $featuredcourseids = self::normalize_ids($featuredcourseids);
        $categoryids = self::normalize_ids($categoryids);

        $courseids = frontpage_repository::get_visible_course_ids(
            $featuredcourseids,
            self::FEATURED_COURSE_LIMIT
        );
        $courses = self::export_course_cards($courseids);
        $categories = self::export_categories(
            frontpage_repository::get_visible_categories($categoryids, 8)
        );
        $partners = self::export_partners();

        return [
            'statistics' => self::get_statistics(),
            'courses' => $courses,
            'hascourses' => !empty($courses),
            'categories' => $categories,
            'hascategories' => !empty($categories),
            'partners' => $partners,
            'haspartners' => !empty($partners),
            'catalogurl' => (new moodle_url('/local/edukav/catalog.php'))->out(false),
        ];
    }

    /**
     * Build one page of the public course catalogue.
     *
     * @param int $page
     * @param int $perpage
     * @param int $categoryid
     * @param string $level
     * @param string $search
     * @return array
     */
    public static function get_catalogue_data(
        int $page = 0,
        int $perpage = 12,
        int $categoryid = 0,
        string $level = '',
        string $search = ''
    ): array {
        $page = max(0, $page);
        $perpage = max(1, min(48, $perpage));
        $categoryid = max(0, $categoryid);
        $level = in_array($level, self::CATALOGUE_LEVELS, true) ? $level : '';
        $search = trim(\core_text::substr($search, 0, 100));
        $total = frontpage_repository::count_visible_courses($categoryid, $level, $search);
        $allcoursetotal = frontpage_repository::count_visible_courses();
        $courseids = frontpage_repository::get_visible_course_ids(
            [],
            $perpage,
            $page * $perpage,
            $categoryid,
            $level,
            $search
        );
        $categories = self::export_categories(frontpage_repository::get_visible_categories());
        foreach ($categories as &$category) {
            $category['isactive'] = $category['id'] === $categoryid;
            $category['url'] = (new moodle_url('/local/edukav/catalog.php', array_filter([
                'categoryid' => $category['id'],
                'level' => $level,
                'q' => $search,
            ], static fn($value): bool => $value !== '')))->out(false);
        }
        unset($category);

        $commonparams = array_filter([
            'categoryid' => $categoryid > 0 ? $categoryid : '',
            'q' => $search,
        ], static fn($value): bool => $value !== '');
        $levelcounts = frontpage_repository::get_visible_course_level_counts($categoryid, $search);
        $levels = [];
        foreach (self::CATALOGUE_LEVELS as $levelvalue) {
            $levels[] = [
                'value' => $levelvalue,
                'label' => get_string('level:' . $levelvalue, 'format_edukav'),
                'count' => $levelcounts[$levelvalue] ?? 0,
                'isactive' => $level === $levelvalue,
                'url' => (new moodle_url('/local/edukav/catalog.php',
                    $commonparams + ['level' => $levelvalue]))->out(false),
            ];
        }

        $allcategoryparams = array_filter([
            'level' => $level,
            'q' => $search,
        ], static fn($value): bool => $value !== '');

        return [
            'courses' => self::export_course_cards($courseids),
            'hascourses' => !empty($courseids),
            'totalcourses' => $total,
            'allcoursetotal' => $allcoursetotal,
            'categories' => $categories,
            'hascategories' => !empty($categories),
            'allcategoriesactive' => $categoryid === 0,
            'allcategoriesurl' => (new moodle_url('/local/edukav/catalog.php', $allcategoryparams))->out(false),
            'levels' => $levels,
            'alllevelsactive' => $level === '',
            'alllevelsurl' => (new moodle_url('/local/edukav/catalog.php', $commonparams))->out(false),
            'alllevelscount' => frontpage_repository::count_visible_courses($categoryid, '', $search),
            'currentcategoryid' => $categoryid,
            'currentlevel' => $level,
            'searchquery' => $search,
            'filtersactive' => $categoryid > 0 || $level !== '' || $search !== '',
            'searchurl' => (new moodle_url('/local/edukav/catalog.php'))->out(false),
            'clearfiltersurl' => (new moodle_url('/local/edukav/catalog.php'))->out(false),
            'page' => $page,
            'perpage' => $perpage,
            'categoryid' => $categoryid,
        ];
    }

    /** Purge the aggregate statistics cache. */
    public static function purge_statistics_cache(): void {
        cache::make('local_edukav', 'frontpage_stats')->purge();
    }

    /**
     * Parse a comma-separated setting into positive unique ids.
     *
     * @param string|null $value
     * @return int[]
     */
    public static function parse_id_setting(?string $value): array {
        return self::normalize_ids(preg_split('/[\s,;]+/', trim((string)$value)) ?: []);
    }

    /**
     * @param array $ids
     * @return int[]
     */
    private static function normalize_ids(array $ids): array {
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, static fn(int $id): bool => $id > 0);
        return array_values(array_unique($ids));
    }

    /**
     * @param int[] $courseids
     * @return array
     */
    private static function export_course_cards(array $courseids): array {
        $cards = [];
        foreach ($courseids as $courseid) {
            try {
                $course = get_course($courseid);
                $category = core_course_category::get($course->category, IGNORE_MISSING, true);
                if (!$course->visible || !$category || !$category->visible ||
                        !core_course_category::can_view_course_info($course)) {
                    continue;
                }

                $card = course_detail_service::get_course_card($courseid);
                $partner = partners_service::get_course_partner_branding($courseid);
                $card['courseurl'] = !empty($card['isenrolled'])
                    ? (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false)
                    : (new moodle_url('/local/edukav/course_detail.php', ['id' => $courseid]))->out(false);
                $card['partner'] = [
                    'id' => (int)($partner['id'] ?? 0),
                    'name' => trim((string)($partner['name'] ?? '')),
                    'logo' => trim((string)($partner['logo'] ?? '')),
                ];
                $cards[] = $card;
            } catch (\Throwable $exception) {
                debugging($exception->getMessage(), DEBUG_DEVELOPER);
            }
        }
        return $cards;
    }

    /**
     * @param array $records
     * @return array
     */
    private static function export_categories(array $records): array {
        $icons = ['bi-laptop', 'bi-person-workspace', 'bi-lightbulb', 'bi-briefcase', 'bi-book', 'bi-stars'];
        $categories = [];
        foreach (array_values($records) as $position => $record) {
            $context = context_coursecat::instance((int)$record->id);
            $categories[] = [
                'id' => (int)$record->id,
                'name' => format_string($record->name, true, ['context' => $context]),
                'description' => format_text($record->description, $record->descriptionformat, [
                    'context' => $context,
                    'filter' => true,
                    'para' => false,
                ]),
                'coursecount' => (int)$record->coursecount,
                'icon' => $icons[$position % count($icons)],
                'url' => (new moodle_url('/local/edukav/catalog.php', [
                    'categoryid' => (int)$record->id,
                ]))->out(false),
            ];
        }
        return $categories;
    }

    /**
     * @return array
     */
    private static function export_partners(): array {
        $partners = [];
        foreach (partners_service::get_all_partners(true) as $partner) {
            $logo = partners_service::get_partner_logo_url((int)$partner->id);
            $partners[] = [
                'id' => (int)$partner->id,
                'name' => format_string($partner->name),
                'logo' => $logo,
                'haslogo' => $logo !== '',
            ];
        }
        return $partners;
    }
}
