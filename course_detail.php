<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

defined('MOODLE_INTERNAL') || die();

use local_edukav\service\course_detail_service;

$courseid = required_param('id', PARAM_INT);

// El detalle público está disponible únicamente para visitantes y usuarios invitado.
if (isloggedin() && !isguestuser()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

$course = get_course($courseid);

$PAGE->set_url(new moodle_url('/local/edukav/course_detail.php', ['id' => $courseid]));
// El frontpage construye el navbar desde el contexto del sitio. El detalle
// sigue consultando el contexto del curso en el servicio, pero usa el mismo
// contexto de navegación para conservar los enlaces y sus permisos de Moodle.
$PAGE->set_context(context_system::instance());
$PAGE->set_pagetype('site-index');
$PAGE->set_pagelayout('course_detail');
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));

$course_detail = course_detail_service::get_course_detail($courseid);

require($CFG->dirroot . '/theme/edukav/layout/course_detail.php');
