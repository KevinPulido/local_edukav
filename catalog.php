<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');

use local_edukav\service\frontpage_service;

$page = optional_param('page', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$level = optional_param('level', '', PARAM_ALPHA);
$search = optional_param('q', '', PARAM_TEXT);
$perpage = 6;
$baseurl = new moodle_url('/local/edukav/catalog.php');
if ($categoryid > 0) {
    $baseurl->param('categoryid', $categoryid);
}
if ($level !== '') {
    $baseurl->param('level', $level);
}
if (trim($search) !== '') {
    $baseurl->param('q', trim($search));
}

$themeconfig = get_config('theme_edukav');
$catalogtitle = trim((string)($themeconfig->catalog_title ?? ''));
if ($catalogtitle === '') {
    $catalogtitle = get_string('catalog_title_default', 'theme_edukav');
}
$catalogintro = trim((string)($themeconfig->catalog_intro ?? ''));
if ($catalogintro === '') {
    $catalogintro = get_string('catalog_intro_default', 'theme_edukav');
}
$catalogempty = trim((string)($themeconfig->catalog_empty ?? ''));
if ($catalogempty === '') {
    $catalogempty = get_string('catalog_empty_default', 'theme_edukav');
}

$PAGE->set_url($baseurl, ['page' => $page]);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('edukav');
$PAGE->set_primary_active_tab('theme_edukav_course_catalogue');
$PAGE->set_title($catalogtitle);
$PAGE->set_heading($catalogtitle);

$catalogue = frontpage_service::get_catalogue_data($page, $perpage, $categoryid, $level, $search);
$catalogue['title'] = $catalogtitle;
$catalogue['intro'] = $catalogintro;
$catalogue['emptytext'] = $catalogempty;
$catalogue['filtertitle'] = get_string('catalog_filter_title', 'theme_edukav');
$catalogue['allcategories'] = get_string('catalog_all_categories', 'theme_edukav');
$catalogue['leveltitle'] = get_string('catalog_level_title', 'theme_edukav');
$catalogue['alllevels'] = get_string('catalog_all_levels', 'theme_edukav');
$catalogue['resultscount'] = get_string('catalog_courses_available', 'theme_edukav', $catalogue['totalcourses']);
$catalogue['pagingbar'] = $OUTPUT->paging_bar(
    $catalogue['totalcourses'],
    $page,
    $perpage,
    $baseurl
);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_edukav/edukav/catalog/catalog', $catalogue);
echo $OUTPUT->footer();
