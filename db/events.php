<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\\core\\event\\course_deleted',
        'callback' => '\\local_edukav\\observer::course_deleted',
    ],
    [
        'eventname' => '\\core\\event\\course_created',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\course_updated',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\course_category_created',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\course_category_updated',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\course_category_deleted',
        'callback' => '\\local_edukav\\observer::course_category_deleted',
    ],
    [
        'eventname' => '\\core\\event\\user_enrolment_created',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\user_enrolment_updated',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\user_enrolment_deleted',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\enrol_instance_created',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\enrol_instance_updated',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\enrol_instance_deleted',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
    [
        'eventname' => '\\core\\event\\user_updated',
        'callback' => '\\local_edukav\\observer::invalidate_frontpage_cache',
    ],
];
