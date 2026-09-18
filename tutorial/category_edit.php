<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/formslib.php');

use local_edukav\form\tutorial_category_form;
use local_edukav\repository\tutorial_categories_repository;
use local_edukav\service\tutorial_categories_service;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$id = optional_param('id', 0, PARAM_INT);
$url = new moodle_url('/local/edukav/tutorial/category_edit.php', $id ? ['id' => $id] : []);
$returnurl = new moodle_url('/local/edukav/tutorial/categories.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$title = $id ? get_string('edittutorialcategory', 'local_edukav') :
    get_string('addtutorialcategory', 'local_edukav');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$form = new tutorial_category_form($url);
if ($id) {
    $form->set_data(tutorial_categories_repository::get_by_id($id));
}
if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    tutorial_categories_service::save($data);
    redirect($returnurl, get_string('tutorialcategorysaved', 'local_edukav'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$form->display();
echo $OUTPUT->footer();
