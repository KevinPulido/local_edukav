<?php
namespace local_edukav\form;

defined('MOODLE_INTERNAL') || die();

class faq_category_form extends \moodleform {
    public function definition(): void {
        $mform = $this->_form;
        $icons = [
            'grid' => get_string('faqicon_grid', 'local_edukav'),
            'person' => get_string('faqicon_user', 'local_edukav'),
            'book' => get_string('faqicon_book', 'local_edukav'),
            'file-earmark-text' => get_string('faqicon_file', 'local_edukav'),
            'award' => get_string('faqicon_award', 'local_edukav'),
            'credit-card' => get_string('faqicon_payment', 'local_edukav'),
            'headset' => get_string('faqicon_support', 'local_edukav'),
            'info-circle' => get_string('faqicon_info', 'local_edukav'),
            'question-circle' => get_string('faqicon_help', 'local_edukav'),
        ];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('text', 'name', get_string('faqcategoryname', 'local_edukav'), ['size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('select', 'icon', get_string('faqcategoryicon', 'local_edukav'), $icons);
        $mform->setDefault('icon', 'question-circle');
        $mform->addElement('advcheckbox', 'visible', get_string('visible', 'local_edukav'));
        $mform->setDefault('visible', 1);
        $mform->addElement('text', 'sortorder', get_string('faqsortorder', 'local_edukav'), ['size' => 8]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);
        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
