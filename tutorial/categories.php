<?php
require_once(__DIR__ . '/../../../config.php');

use local_edukav\repository\tutorial_categories_repository;
use local_edukav\service\tutorial_categories_service;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$url = new moodle_url('/local/edukav/tutorial/categories.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managetutorialcategories', 'local_edukav'));
$PAGE->set_heading(get_string('managetutorialcategories', 'local_edukav'));

$deleteid = optional_param('delete', 0, PARAM_INT);
if ($deleteid) {
    $category = tutorial_categories_repository::get_by_id($deleteid);
    if (optional_param('confirm', 0, PARAM_BOOL) && confirm_sesskey()) {
        try {
            tutorial_categories_service::delete($deleteid);
            redirect($url, get_string('tutorialcategorydeleted', 'local_edukav'), null,
                \core\output\notification::NOTIFY_SUCCESS);
        } catch (moodle_exception $exception) {
            redirect($url, $exception->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
        }
    }
    echo $OUTPUT->header();
    $confirmurl = new moodle_url($url, ['delete' => $deleteid, 'confirm' => 1, 'sesskey' => sesskey()]);
    echo $OUTPUT->confirm(
        get_string('tutorialcategorydeleteconfirm', 'local_edukav', format_string($category->name)),
        $confirmurl,
        $url
    );
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managetutorialcategories', 'local_edukav'));
echo html_writer::start_div('mb-3');
echo $OUTPUT->single_button(
    new moodle_url('/local/edukav/tutorial/category_edit.php'),
    get_string('addtutorialcategory', 'local_edukav'),
    'get',
    ['type' => 'primary']
);
echo ' ' . html_writer::link(
    new moodle_url('/local/edukav/tutorial/index.php'),
    get_string('managetutorials', 'local_edukav'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();

$categories = tutorial_categories_repository::get_all();
$table = new html_table();
$table->head = [
    get_string('tutorialcategoryname', 'local_edukav'),
    get_string('tutorialcategoryicon', 'local_edukav'),
    get_string('tutorialcount', 'local_edukav'),
    get_string('status', 'local_edukav'),
    get_string('tutorialsortorder', 'local_edukav'),
    get_string('actions', 'local_edukav'),
];
foreach ($categories as $category) {
    $edit = new moodle_url('/local/edukav/tutorial/category_edit.php', ['id' => $category->id]);
    $delete = new moodle_url($url, ['delete' => $category->id]);
    $actions = html_writer::link($edit, get_string('edit'), ['class' => 'btn btn-border btn-sm']) . ' · ' .
        html_writer::link($delete, get_string('delete'), ['class' => 'btn btn-secondary btn-sm']);
    $status = $category->visible ? get_string('visible', 'local_edukav') : get_string('hidden', 'local_edukav');
    $table->data[] = [
        format_string($category->name),
        s($category->icon),
        (int) $category->tutorialcount,
        $status,
        (int) $category->sortorder,
        $actions,
    ];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
