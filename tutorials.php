<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once(__DIR__ . '/../../config.php');

use local_edukav\service\tutorials_service;

$context = context_system::instance();
$url = new moodle_url('/local/edukav/tutorials.php');

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('edukav');
$PAGE->set_primary_active_tab('theme_edukav_tutorials');
$PAGE->set_title(get_string('tutorialsnav', 'theme_edukav'));
$PAGE->set_heading(get_string('tutorialsnav', 'theme_edukav'));

$config = get_config('theme_edukav');
$tutorials = tutorials_service::get_tutorials();
$title = trim((string)($config->title_tutorial ?? ''));
$description = trim((string)($config->text_tutorial ?? ''));

$templatecontext = [
    'tutorials' => array_values($tutorials),
    'tutorialcount' => count($tutorials),
    'title_tutorial' => $title !== '' ? $title : get_string('tutorial_page_title', 'theme_edukav'),
    'text_tutorial' => $description !== '' ? $description : get_string('tutorial_page_description', 'theme_edukav'),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_edukav/edukav/tutorials/tutorial', $templatecontext);
echo $OUTPUT->footer();
