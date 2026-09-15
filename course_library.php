<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/forms/course_library_item_form.php');

use local_edukav\forms\course_library_item_form;
use local_edukav\service\course_library_service;

$courseid = required_param('courseid', PARAM_INT);
$resourceid = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course);
require_capability(course_library_service::CAPABILITY_MANAGE, $context);

$baseurl = new moodle_url('/local/edukav/course_library.php', ['courseid' => $courseid]);
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('drawer');
$PAGE->set_title(get_string('manage_course_library', 'local_edukav'));
$PAGE->set_heading(format_string($course->fullname));

if ($action === 'delete' && $resourceid) {
    require_sesskey();
    course_library_service::delete_resource($resourceid, $courseid);
    redirect($baseurl, get_string('resource_deleted', 'local_edukav'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$formdata = new stdClass();
$formdata->courseid = $courseid;
$formdata->id = 0;
$formdata->visible = 1;
$formdata->resourcetype = 'document';
$formdata->level = 'basic';
$formdata->sortorder = 0;

if ($resourceid) {
    $resource = course_library_service::get_resource($resourceid, $courseid);
    if (!$resource) {
        throw new moodle_exception('resource_not_found', 'local_edukav');
    }
    $formdata = clone $resource;
}

$resourceoptions = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['*']];
$coveroptions = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['image']];
$formdata->resourcefile = file_get_submitted_draft_itemid('resourcefile');
file_prepare_draft_area(
    $formdata->resourcefile,
    $context->id,
    'local_edukav',
    course_library_service::FILEAREA_RESOURCE,
    $resourceid,
    $resourceoptions
);
$formdata->coverimage = file_get_submitted_draft_itemid('coverimage');
file_prepare_draft_area(
    $formdata->coverimage,
    $context->id,
    'local_edukav',
    course_library_service::FILEAREA_COVER,
    $resourceid,
    $coveroptions
);

$form = new course_library_item_form($baseurl);
$form->set_data($formdata);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/edukav/library.php', ['courseid' => $courseid]));
}
if ($data = $form->get_data()) {
    course_library_service::save_resource($courseid, $data);
    redirect($baseurl, get_string('resource_saved', 'local_edukav'), null, \core\output\notification::NOTIFY_SUCCESS);
}

ob_start();
$form->display();
$formhtml = ob_get_clean();
$library = course_library_service::get_library_context($courseid);
foreach ($library['resources'] as &$item) {
    $item['deleteurl'] = (new moodle_url('/local/edukav/course_library.php', [
        'courseid' => $courseid,
        'id' => $item['id'],
        'action' => 'delete',
        'sesskey' => sesskey(),
    ]))->out(false);
}
unset($item);

$templatecontext = [
    'courseid' => $courseid,
    'coursename' => format_string($course->fullname),
    'courseurl' => (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false),
    'libraryurl' => (new moodle_url('/local/edukav/library.php', ['courseid' => $courseid]))->out(false),
    'editing' => $resourceid > 0,
    'formhtml' => $formhtml,
    'resources' => $library['resources'],
    'hasresources' => $library['hasresources'],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_edukav/edukav/course_library/manage', $templatecontext);
echo $OUTPUT->footer();
