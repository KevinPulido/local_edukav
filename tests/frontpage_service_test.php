<?php
// This file is part of Moodle - http://moodle.org/

namespace local_edukav;

defined('MOODLE_INTERNAL') || die();

use local_edukav\repository\frontpage_repository;
use local_edukav\service\frontpage_service;

/** Tests for public frontpage data and aggregate statistics. */
final class frontpage_service_test extends \advanced_testcase {

    public function test_statistics_respect_visibility_and_active_enrolments(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $category = $generator->create_category(['visible' => 1]);
        $othercategory = $generator->create_category(['visible' => 1]);
        $courseone = $generator->create_course(['category' => $category->id, 'visible' => 1]);
        $coursetwo = $generator->create_course(['category' => $othercategory->id, 'visible' => 1]);
        $generator->create_course(['category' => $category->id, 'visible' => 0]);

        $activeuser = $generator->create_user();
        $expireduser = $generator->create_user();
        $suspendeduser = $generator->create_user(['suspended' => 1]);
        $generator->enrol_user($activeuser->id, $courseone->id, 'student');
        $generator->enrol_user($activeuser->id, $coursetwo->id, 'student');
        $generator->enrol_user($expireduser->id, $courseone->id, 'student', 'manual', 0, time() - 60);
        $generator->enrol_user($suspendeduser->id, $courseone->id, 'student');

        frontpage_service::purge_statistics_cache();
        $statistics = frontpage_service::get_statistics();

        $this->assertSame(2, $statistics['courses']);
        $this->assertSame(1, $statistics['learners']);
        $this->assertSame(2, $statistics['categories']);
    }

    public function test_selected_courses_are_limited_and_keep_configured_order(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $category = $generator->create_category(['visible' => 1]);
        $courseone = $generator->create_course(['category' => $category->id, 'visible' => 1]);
        $coursetwo = $generator->create_course(['category' => $category->id, 'visible' => 1]);
        $hidden = $generator->create_course(['category' => $category->id, 'visible' => 0]);

        $ids = frontpage_repository::get_visible_course_ids([
            $coursetwo->id,
            $hidden->id,
            $courseone->id,
        ], 6);

        $this->assertSame([(int)$coursetwo->id, (int)$courseone->id], $ids);
    }
}
