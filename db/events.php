<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\\core\\event\\course_deleted',
        'callback' => '\\local_edukav\\observer::course_deleted',
    ],
];
