<?php
namespace local_edukav\form;

defined('MOODLE_INTERNAL') || die();

class faq_form extends \moodleform {
    public function definition(): void {
        $mform = $this->_form;
        $categories = $this->_customdata['categories'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('select', 'categoryid', get_string('faqcategory', 'local_edukav'), $categories);
        $mform->addRule('categoryid', null, 'required', null, 'client');
        $mform->setType('categoryid', PARAM_INT);
        $mform->addElement('text', 'question', get_string('faqquestion', 'local_edukav'), ['size' => 80]);
        $mform->setType('question', PARAM_TEXT);
        $mform->addRule('question', null, 'required', null, 'client');
        $mform->addElement('textarea', 'answer', get_string('faqanswer', 'local_edukav'),
            ['rows' => 8, 'cols' => 80]);
        $mform->setType('answer', PARAM_TEXT);
        $mform->addRule('answer', null, 'required', null, 'client');
        $mform->addElement('advcheckbox', 'visible', get_string('visible', 'local_edukav'));
        $mform->setDefault('visible', 1);
        $mform->addElement('text', 'sortorder', get_string('faqsortorder', 'local_edukav'), ['size' => 8]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);
        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
