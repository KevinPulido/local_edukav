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
 * Form for configuring a course category's visual presentation.
 *
 * @package    local_edukav
 * @copyright  2026 Edukav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_edukav\form;

defined('MOODLE_INTERNAL') || die();

use html_writer;
use local_edukav\service\category_styles_service;

/** Form for a course category's visual presentation. */
class category_style_form extends \moodleform {
    public function definition(): void {
        $mform = $this->_form;
        $categoryname = $this->_customdata['categoryname'] ?? '';
        $currentimage = $this->_customdata['currentimage'] ?? '';
        $currenticonimage = $this->_customdata['currenticonimage'] ?? '';

        $mform->addElement('hidden', 'categoryid');
        $mform->setType('categoryid', PARAM_INT);
        $mform->addElement('static', 'categoryname', get_string('category'), format_string($categoryname));

        $mform->addElement('select', 'icontype', get_string('categorystyleicontype', 'local_edukav'), [
            'bootstrap' => get_string('categorystyleiconbootstrap', 'local_edukav'),
            'image' => get_string('categorystyleiconcustom', 'local_edukav'),
        ]);
        $mform->setType('icontype', PARAM_ALPHA);

        $mform->addElement('select', 'icon', get_string('categorystyleiconbootstrap', 'local_edukav'),
            category_styles_service::get_icon_options());
        $mform->setType('icon', PARAM_ALPHANUMEXT);
        $mform->hideIf('icon', 'icontype', 'eq', 'image');

        $mform->addElement('filemanager', 'categoryiconimage',
            get_string('categorystyleiconcustom', 'local_edukav'), null, [
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => ['image'],
            ]);
        $mform->setType('categoryiconimage', PARAM_INT);
        $mform->hideIf('categoryiconimage', 'icontype', 'neq', 'image');

        if ($currenticonimage !== '') {
            $preview = html_writer::empty_tag('img', [
                'src' => $currenticonimage,
                'alt' => '',
                'style' => 'width:64px;height:64px;object-fit:cover;border-radius:50%;',
            ]);
            $mform->addElement('static', 'currenticonimage',
                get_string('categorystylecurrenticonimage', 'local_edukav'), $preview);
            $mform->hideIf('currenticonimage', 'icontype', 'neq', 'image');
        }

        $mform->addElement('filemanager', 'categoryimage', get_string('categorystyleimage', 'local_edukav'), null, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]);
        $mform->setType('categoryimage', PARAM_INT);

        if ($currentimage !== '') {
            $preview = html_writer::empty_tag('img', [
                'src' => $currentimage,
                'alt' => '',
                'style' => 'width:120px;height:90px;object-fit:cover;border-radius:12px;',
            ]);
            $mform->addElement('static', 'currentimage', get_string('categorystylecurrentimage', 'local_edukav'),
                $preview);
        }

        $mform->addElement('text', 'imagealt', get_string('categorystyleimagealt', 'local_edukav'), ['size' => 70]);
        $mform->setType('imagealt', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (($data['icontype'] ?? 'bootstrap') === 'image') {
            $draftitemid = (int)($data['categoryiconimage'] ?? 0);
            if ($draftitemid <= 0 || empty(file_get_all_files_in_draftarea($draftitemid))) {
                $errors['categoryiconimage'] = get_string('categorystyleiconimagerequired', 'local_edukav');
            }
        }
        return $errors;
    }
}
