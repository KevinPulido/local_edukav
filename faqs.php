<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once(__DIR__ . '/../../config.php');

use local_edukav\repository\faq_categories_repository;
use local_edukav\repository\faqs_repository;

$context = context_system::instance();
$url = new moodle_url('/local/edukav/faqs.php');

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('edukav');
$PAGE->set_primary_active_tab('theme_edukav_help');
$PAGE->set_title(get_string('faq_page_title', 'theme_edukav'));
$PAGE->set_heading(get_string('faq_page_title', 'theme_edukav'));

$config = get_config('theme_edukav');
$faqs = faqs_repository::get_visible();
$categories = faq_categories_repository::get_visible();
$title = trim((string)($config->title_faq ?? ''));
$description = trim((string)($config->text_faq ?? ''));

$templatecontext = [
    'faqs' => array_values($faqs),
    'categories' => array_values($categories),
    'contacturl' => new moodle_url('/theme/edukav/layout/contact.php'),
    'title_faq' => $title !== '' ? $title : get_string('faq_page_title', 'theme_edukav'),
    'text_faq' => $description !== '' ? $description : get_string('faq_page_description', 'theme_edukav'),
    'faqcount' => count($faqs),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_edukav/edukav/faqs/faq', $templatecontext);
echo $OUTPUT->footer();
