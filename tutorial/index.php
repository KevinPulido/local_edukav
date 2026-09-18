<?php
require_once(__DIR__ . '/../../../config.php');

use local_edukav\repository\tutorials_repository;
use local_edukav\service\tutorials_service;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$url = new moodle_url('/local/edukav/tutorial/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managetutorials', 'local_edukav'));
$PAGE->set_heading(get_string('managetutorials', 'local_edukav'));

$deleteid = optional_param('delete', 0, PARAM_INT);
if ($deleteid) {
    $tutorial = tutorials_repository::get_by_id($deleteid);
    if (optional_param('confirm', 0, PARAM_BOOL) && confirm_sesskey()) {
        tutorials_service::delete($deleteid);
        redirect($url, get_string('tutorialdeleted', 'local_edukav'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }

    echo $OUTPUT->header();
    $confirmurl = new moodle_url($url, ['delete' => $deleteid, 'confirm' => 1, 'sesskey' => sesskey()]);
    echo $OUTPUT->confirm(
        get_string('tutorialdeleteconfirm', 'local_edukav', format_string($tutorial->title)),
        $confirmurl,
        $url
    );
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managetutorials', 'local_edukav'));
echo html_writer::start_div('mb-3');
echo $OUTPUT->single_button(
    new moodle_url('/local/edukav/tutorial/edit.php'),
    get_string('addtutorial', 'local_edukav'),
    'get',
    ['type' => 'primary']
);
echo ' ' . html_writer::link(
    new moodle_url('/local/edukav/tutorial/categories.php'),
    get_string('managetutorialcategories', 'local_edukav'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();

$tutorials = tutorials_repository::get_all();
if (!$tutorials) {
    echo $OUTPUT->notification(get_string('notutorials', 'local_edukav'),
        \core\output\notification::NOTIFY_INFO);
} else {
    $table = new html_table();
    $table->head = [
        get_string('tutorialtitle', 'local_edukav'),
        get_string('tutorialcategory', 'local_edukav'),
        get_string('tutorialurl', 'local_edukav'),
        get_string('status', 'local_edukav'),
        get_string('tutorialsortorder', 'local_edukav'),
        get_string('actions', 'local_edukav'),
    ];
    foreach ($tutorials as $tutorial) {
        $editurl = new moodle_url('/local/edukav/tutorial/edit.php', ['id' => $tutorial->id]);
        $deleteurl = new moodle_url($url, ['delete' => $tutorial->id]);
        $actions = html_writer::link($editurl, get_string('edit'), ['class' => 'btn btn-border btn-sm']) . ' · ' .
            html_writer::link($deleteurl, get_string('delete'), ['class' => 'btn btn-secondary btn-sm']);
        $videourl = html_writer::link($tutorial->url, get_string('view'), [
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ]);
        $table->data[] = [
            format_string($tutorial->title),
            format_string($tutorial->categoryname),
            $videourl,
            $tutorial->visible ? get_string('visible', 'local_edukav') : get_string('hidden', 'local_edukav'),
            (int) $tutorial->sortorder,
            $actions,
        ];
    }
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
