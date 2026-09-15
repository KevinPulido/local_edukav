<?php
namespace local_edukav;

defined('MOODLE_INTERNAL') || die();

use local_edukav\service\course_library_service;

/** Event handlers for local_edukav data tied to courses. */
class observer {
    public static function course_deleted(\core\event\course_deleted $event): void {
        course_library_service::delete_course_resources((int)$event->objectid, (int)$event->contextid);
    }
}
