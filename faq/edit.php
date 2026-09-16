<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/formslib.php');

use local_edukav\form\faq_form;
use local_edukav\repository\faq_categories_repository;
use local_edukav\repository\faqs_repository;
use local_edukav\service\faqs_service;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$id = optional_param('id', 0, PARAM_INT);
$url = new moodle_url('/local/edukav/faq/edit.php', $id ? ['id' => $id] : []);
$returnurl = new moodle_url('/local/edukav/faq/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$title = $id ? get_string('editfaq', 'local_edukav') : get_string('addfaq', 'local_edukav');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$categories = faq_categories_repository::get_options();
if (!$categories) {
    redirect(new moodle_url('/local/edukav/faq/category_edit.php'), get_string('createcategoryfirst', 'local_edukav'));
}
$form = new faq_form($url, ['categories' => $categories]);
if ($id) {
    $form->set_data(faqs_repository::get_by_id($id));
}
if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    faqs_service::save($data);
    redirect($returnurl, get_string('faqsaved', 'local_edukav'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$form->display();
echo $OUTPUT->footer();
