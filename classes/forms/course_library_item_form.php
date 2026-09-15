<?php
namespace local_edukav\forms;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use local_edukav\service\course_library_service;

/** Form used by course editors to create and update library resources. */
class course_library_item_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'title', get_string('resource_title', 'local_edukav'), ['size' => 60]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('resource_description', 'local_edukav'), [
            'rows' => 4,
            'class' => 'w-100',
        ]);
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('text', 'category', get_string('resource_category', 'local_edukav'), ['size' => 40]);
        $mform->setType('category', PARAM_TEXT);

        $mform->addElement(
            'select',
            'resourcetype',
            get_string('resource_type', 'local_edukav'),
            course_library_service::get_type_options()
        );
        $mform->addElement(
            'select',
            'level',
            get_string('resource_level', 'local_edukav'),
            course_library_service::get_level_options()
        );

        $mform->addElement('url', 'externalurl', get_string('resource_url', 'local_edukav'), ['size' => 60]);
        $mform->setType('externalurl', PARAM_URL);

        $mform->addElement('filemanager', 'resourcefile', get_string('resource_file', 'local_edukav'), null, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['*'],
        ]);
        $mform->addElement('filemanager', 'coverimage', get_string('resource_cover', 'local_edukav'), null, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]);

        $mform->addElement('text', 'sortorder', get_string('resource_sortorder', 'local_edukav'), ['size' => 8]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);
        $mform->addElement('advcheckbox', 'featured', get_string('resource_featured', 'local_edukav'));
        $mform->addElement('advcheckbox', 'visible', get_string('resource_visible', 'local_edukav'));
        $mform->setDefault('visible', 1);

        $this->add_action_buttons(true, get_string('save_resource', 'local_edukav'));
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $hasurl = trim((string)($data['externalurl'] ?? '')) !== '';
        $draftfiles = !empty($data['resourcefile'])
            ? file_get_all_files_in_draftarea((int)$data['resourcefile'])
            : [];
        if (!$hasurl && empty($draftfiles)) {
            $errors['externalurl'] = get_string('resource_source_required', 'local_edukav');
        }
        return $errors;
    }
}
