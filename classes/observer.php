<?php
namespace local_edukav;

defined('MOODLE_INTERNAL') || die();

use local_edukav\service\course_library_service;
use local_edukav\service\frontpage_service;

/** Event handlers for local_edukav data tied to courses. */
class observer {
    public static function course_deleted(\core\event\course_deleted $event): void {
        course_library_service::delete_course_resources((int)$event->objectid, (int)$event->contextid);
        frontpage_service::purge_statistics_cache();
    }

    /**
     * Invalidate cached aggregates after a relevant Moodle event.
     *
     * @param \core\event\base $event
     * @return void
     */
    public static function invalidate_frontpage_cache(\core\event\base $event): void {
        frontpage_service::purge_statistics_cache();
    }
}
