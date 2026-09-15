<?php
require_once(__DIR__ . '/../../config.php');

use local_edukav\service\course_library_service;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course);

$PAGE->set_url(new moodle_url('/local/edukav/library.php', ['courseid' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('drawer');
$PAGE->set_pagetype('local-edukav-library');
$PAGE->set_title(get_string('course_library', 'local_edukav'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('course_library', 'local_edukav'));

$librarycontext = course_library_service::get_library_context($courseid);
$librarycontext['coursename'] = format_string($course->fullname);
$librarycontext['courseurl'] = (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false);

$templatecontext = [
    'courseid' => $courseid,
    'library' => $librarycontext,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_edukav/edukav/course_library/library_page', $templatecontext);
echo $OUTPUT->footer();
