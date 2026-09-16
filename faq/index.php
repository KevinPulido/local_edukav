<?php
require_once(__DIR__ . '/../../../config.php');

use local_edukav\repository\faqs_repository;
use local_edukav\service\faqs_service;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$url = new moodle_url('/local/edukav/faq/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managefaqs', 'local_edukav'));
$PAGE->set_heading(get_string('managefaqs', 'local_edukav'));

$deleteid = optional_param('delete', 0, PARAM_INT);
if ($deleteid) {
    $faq = faqs_repository::get_by_id($deleteid);
    if (optional_param('confirm', 0, PARAM_BOOL) && confirm_sesskey()) {
        faqs_service::delete($deleteid);
        redirect($url, get_string('faqdeleted', 'local_edukav'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    echo $OUTPUT->header();
    $confirmurl = new moodle_url($url, ['delete' => $deleteid, 'confirm' => 1, 'sesskey' => sesskey()]);
    echo $OUTPUT->confirm(get_string('faqdeleteconfirm', 'local_edukav', format_string($faq->question)),
        $confirmurl, $url);
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managefaqs', 'local_edukav'));
echo html_writer::start_div('mb-3');
echo $OUTPUT->single_button(new moodle_url('/local/edukav/faq/edit.php'),
    get_string('addfaq', 'local_edukav'), 'get', ['type' => 'primary']);
echo ' ' . html_writer::link(new moodle_url('/local/edukav/faq/categories.php'),
    get_string('managefaqcategories', 'local_edukav'), ['class' => 'btn btn-secondary']);
echo html_writer::end_div();

$faqs = faqs_repository::get_all();
if (!$faqs) {
    echo $OUTPUT->notification(get_string('nofaqs', 'local_edukav'), \core\output\notification::NOTIFY_INFO);
} else {
    $table = new html_table();
    $table->head = [get_string('faqquestion', 'local_edukav'), get_string('faqcategory', 'local_edukav'),
        get_string('status', 'local_edukav'), get_string('faqsortorder', 'local_edukav'),
        get_string('actions', 'local_edukav')];
    foreach ($faqs as $faq) {
        $edit = new moodle_url('/local/edukav/faq/edit.php', ['id' => $faq->id]);
        $delete = new moodle_url($url, ['delete' => $faq->id]);
        $actions = html_writer::link($edit, get_string('edit'),['class' => 'btn btn-border btn-sm']) . ' · ' .
            html_writer::link($delete, get_string('delete'),['class'=> 'btn btn-secondary btn-sm']);
        $status = $faq->visible ? get_string('visible', 'local_edukav') : get_string('hidden', 'local_edukav');
        $table->data[] = [format_string($faq->question), format_string($faq->categoryname), $status,
            (int) $faq->sortorder, $actions];
    }
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
