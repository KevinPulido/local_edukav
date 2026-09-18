<?php
namespace local_edukav\form;

defined('MOODLE_INTERNAL') || die();

/**
 * Form used to create and edit tutorials.
 *
 * @package    local_edukav
 * @copyright  2025 Kevin Pulido
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tutorial_form extends \moodleform {
    /**
     * Defines the tutorial fields.
     */
    public function definition(): void {
        $mform = $this->_form;
        $categories = $this->_customdata['categories'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('select', 'categoryid', get_string('tutorialcategory', 'local_edukav'), $categories);
        $mform->addRule('categoryid', null, 'required', null, 'client');
        $mform->setType('categoryid', PARAM_INT);

        $mform->addElement('text', 'title', get_string('tutorialtitle', 'local_edukav'), ['size' => 80]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('tutorialdescription', 'local_edukav'),
            ['rows' => 7, 'cols' => 80]);
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');

        $mform->addElement('url', 'url', get_string('tutorialurl', 'local_edukav'), ['size' => 80]);
        $mform->setType('url', PARAM_URL);
        $mform->addRule('url', null, 'required', null, 'client');

        $mform->addElement('advcheckbox', 'visible', get_string('visible', 'local_edukav'));
        $mform->setDefault('visible', 1);

        $mform->addElement('text', 'sortorder', get_string('tutorialsortorder', 'local_edukav'), ['size' => 8]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
