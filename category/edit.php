<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Course category appearance editing page.
 *
 * @package    local_edukav
 * @copyright  2026 Edukav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/formslib.php');

use local_edukav\form\category_style_form;
use local_edukav\repository\category_styles_repository;
use local_edukav\service\category_styles_service;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$categoryid = required_param('categoryid', PARAM_INT);
$category = core_course_category::get($categoryid, MUST_EXIST, true);
$url = new moodle_url('/local/edukav/category/edit.php', ['categoryid' => $categoryid]);
$returnurl = new moodle_url('/local/edukav/category/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('editcategorystyle', 'local_edukav', format_string($category->name)));
$PAGE->set_heading(get_string('editcategorystyle', 'local_edukav', format_string($category->name)));

$style = category_styles_repository::get_by_categoryid($categoryid);
$formdata = $style ? clone $style : (object)[
    'categoryid' => $categoryid,
    'icontype' => 'bootstrap',
    'icon' => 'bi-book',
    'imagealt' => '',
];
$styleid = $style ? (int)$style->id : 0;
$imageoptions = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['image']];
file_prepare_standard_filemanager(
    $formdata,
    'categoryimage',
    $imageoptions,
    $context,
    'local_edukav',
    category_styles_service::FILEAREA_CATEGORY_IMAGE,
    $styleid
);
file_prepare_standard_filemanager(
    $formdata,
    'categoryiconimage',
    $imageoptions,
    $context,
    'local_edukav',
    category_styles_service::FILEAREA_CATEGORY_ICON,
    $styleid
);

$form = new category_style_form($url, [
    'categoryname' => $category->name,
    'currentimage' => category_styles_service::get_image_url($styleid),
    'currenticonimage' => category_styles_service::get_icon_image_url($styleid),
]);
$form->set_data($formdata);
if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    category_styles_service::save($data);
    redirect($returnurl, get_string('categorystylesaved', 'local_edukav'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editcategorystyle', 'local_edukav', format_string($category->name)));
$form->display();
echo $OUTPUT->footer();
