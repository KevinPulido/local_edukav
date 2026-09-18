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
 * Course category appearance management page.
 *
 * @package    local_edukav
 * @copyright  2026 Edukav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use local_edukav\repository\category_styles_repository;
use local_edukav\service\category_styles_service;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$url = new moodle_url('/local/edukav/category/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managecategorystyles', 'local_edukav'));
$PAGE->set_heading(get_string('managecategorystyles', 'local_edukav'));

$resetid = optional_param('reset', 0, PARAM_INT);
if ($resetid) {
    $category = core_course_category::get($resetid, MUST_EXIST, true);
    if (optional_param('confirm', 0, PARAM_BOOL) && confirm_sesskey()) {
        category_styles_service::reset($resetid);
        redirect($url, get_string('categorystyleresetdone', 'local_edukav'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }

    echo $OUTPUT->header();
    $confirmurl = new moodle_url($url, ['reset' => $resetid, 'confirm' => 1, 'sesskey' => sesskey()]);
    echo $OUTPUT->confirm(
        get_string('categorystyleresetconfirm', 'local_edukav', format_string($category->name)),
        $confirmurl,
        $url
    );
    echo $OUTPUT->footer();
    exit;
}

$stylesbycategory = [];
foreach (category_styles_repository::get_all() as $style) {
    $stylesbycategory[(int)$style->categoryid] = $style;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecategorystyles', 'local_edukav'));
echo html_writer::tag('p', get_string('managecategorystylesdesc', 'local_edukav'), ['class' => 'mb-4']);

$table = new html_table();
$table->head = [
    get_string('category'),
    get_string('categorystyledisplaytype', 'local_edukav'),
    get_string('categorystylepreview', 'local_edukav'),
    get_string('actions', 'local_edukav'),
];
foreach (core_course_category::get_all() as $category) {
    $categoryid = (int)$category->id;
    $style = $stylesbycategory[$categoryid] ?? null;
    $editurl = new moodle_url('/local/edukav/category/edit.php', ['categoryid' => $categoryid]);
    $actions = html_writer::link($editurl, get_string('edit'), ['class' => 'btn btn-border btn-sm']);
    $type = get_string('categorystyledefault', 'local_edukav');
    $preview = html_writer::tag('i', '', ['class' => 'bi bi-book', 'aria-hidden' => 'true']);

    if ($style) {
        $reseturl = new moodle_url($url, ['reset' => $categoryid]);
        $actions .= ' · ' . html_writer::link($reseturl, get_string('categorystylereset', 'local_edukav'),
            ['class' => 'btn btn-secondary btn-sm']);
        $imageurl = category_styles_service::get_image_url((int)$style->id);
        $iconimageurl = category_styles_service::get_icon_image_url((int)$style->id);
        $hascustomicon = $style->icontype === 'image' && $iconimageurl !== '';
        $type = get_string($hascustomicon ? 'categorystyleiconcustom' : 'categorystyleiconbootstrap',
            'local_edukav');
        $iconpreview = $hascustomicon
            ? html_writer::empty_tag('img', [
                'src' => $iconimageurl,
                'alt' => '',
                'style' => 'width:48px;height:48px;object-fit:cover;border-radius:50%;',
            ])
            : html_writer::tag('i', '', [
                'class' => 'bi ' . $style->icon,
                'aria-hidden' => 'true',
                'style' => 'font-size:28px;',
            ]);

        if ($imageurl !== '') {
            $type .= ' + ' . get_string('categorystyleimage', 'local_edukav');
            $preview = html_writer::start_div('d-flex align-items-center gap-3') .
                html_writer::empty_tag('img', [
                    'src' => $imageurl,
                    'alt' => $style->imagealt,
                    'style' => 'width:72px;height:52px;object-fit:cover;border-radius:8px;',
                ]) . $iconpreview . html_writer::end_div();
        } else {
            $preview = $iconpreview;
        }
    }

    $table->data[] = [format_string($category->name), $type, $preview, $actions];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
