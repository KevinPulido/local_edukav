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

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

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

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
