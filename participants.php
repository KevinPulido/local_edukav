<?php
// This file is part of Moodle - http://moodle.org/.

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

$courseid = required_param('id', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 30;

$course = get_course($courseid);
require_login($course);

$context = context_course::instance($course->id);
course_require_view_participants($context);

// Users with enrolment management access retain Moodle's complete view.
if (has_capability('moodle/course:enrolreview', $context)) {
    redirect(new moodle_url('/user/index.php', ['id' => $course->id]));
}

$url = new moodle_url('/local/edukav/participants.php', ['id' => $course->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('course-view-participants');
$PAGE->set_title(get_string('participants'));
$PAGE->set_heading($course->fullname);

$fields = implode(',', [
    'u.id',
    'u.firstname',
    'u.lastname',
    'u.firstnamephonetic',
    'u.lastnamephonetic',
    'u.middlename',
    'u.alternatename',
    'u.picture',
    'u.imagealt',
    'u.email',
]);

$users = get_enrolled_users(
    $context,
    '',
    0,
    $fields,
    'u.lastname ASC, u.firstname ASC',
    $page * $perpage,
    $perpage,
    true
);
$total = count_enrolled_users($context, '', 0, true);
$participants = [];

foreach ($users as $user) {
    $roles = role_fix_names(get_user_roles($context, $user->id, true), $context, ROLENAME_ALIAS);
    $rolenames = array_map(static function(stdClass $role): string {
        return $role->localname ?? $role->name ?? $role->shortname;
    }, $roles);

    $participants[] = [
        'picture' => $OUTPUT->user_picture($user, [
            'courseid' => $course->id,
            'size' => 35,
            'link' => false,
            'class' => 'edukav-participants__avatar',
        ]),
        'fullname' => fullname($user),
        'profileurl' => (new moodle_url('/user/view.php', [
            'id' => $user->id,
            'course' => $course->id,
        ]))->out(false),
        'email' => $user->email,
        'roles' => implode(', ', $rolenames),
    ];
}

$templatecontext = [
    'title' => get_string('enrolledusers', 'enrol'),
    'countlabel' => get_string('countparticipantsfound', 'core_user', $total),
    'participants' => $participants,
    'hasparticipants' => !empty($participants),
    'pagination' => $OUTPUT->paging_bar($total, $page, $perpage, $url),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_edukav/edukav/participants/student_list', $templatecontext);
echo $OUTPUT->footer();
