<?php
namespace local_edukav\form;

defined('MOODLE_INTERNAL') || die();

class tutorial_category_form extends \moodleform {
    public function definition(): void {
        $mform = $this->_form;
        $icons = [
            'camera-video' => get_string('tutorialicon_video', 'local_edukav'),
            'play-circle' => get_string('tutorialicon_play', 'local_edukav'),
            'person-video3' => get_string('tutorialicon_presenter', 'local_edukav'),
            'book' => get_string('faqicon_book', 'local_edukav'),
            'lightbulb' => get_string('tutorialicon_idea', 'local_edukav'),
            'gear' => get_string('tutorialicon_settings', 'local_edukav'),
            'question-circle' => get_string('faqicon_help', 'local_edukav'),
        ];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('text', 'name', get_string('tutorialcategoryname', 'local_edukav'), ['size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('select', 'icon', get_string('tutorialcategoryicon', 'local_edukav'), $icons);
        $mform->setDefault('icon', 'camera-video');
        $mform->addElement('advcheckbox', 'visible', get_string('visible', 'local_edukav'));
        $mform->setDefault('visible', 1);
        $mform->addElement('text', 'sortorder', get_string('tutorialsortorder', 'local_edukav'), ['size' => 8]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);
        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
