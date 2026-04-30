<?php
namespace local_edukav\forms;

defined('MOODLE_INTERNAL') || die();

global $CFG;
use html_writer;
require_once($CFG->libdir . '/formslib.php');

class partner_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;
        $currentlogo = $this->_customdata['currentlogo'] ?? '';
        $gradientpreview = $this->_customdata['gradientpreview'] ?? '';

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', 'Nombre');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'slug', 'Slug');
        $mform->setType('slug', PARAM_TEXT);

        $mform->addElement('filemanager', 'logo', 'Logo', null, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]);
        $mform->setType('logo', PARAM_INT);
        if (!empty($currentlogo)) {
            $mform->addElement('static', 'logo_preview', 'Vista previa actual', html_writer::empty_tag('img', [
                'src' => $currentlogo,
                'alt' => 'Logo actual',
                'style' => 'max-width:120px;max-height:72px;object-fit:contain;background:#fff;padding:8px;border-radius:12px;border:1px solid #e5e7eb;',
            ]));
        }

        $mform->addElement('text', 'brand_color', 'Color principal', [
            'id' => 'id_brand_color_text',
            'class' => 'edukav-brand-color-input',
        ]);
        $mform->setType('brand_color', PARAM_RAW_TRIMMED);
        $mform->addElement('static', 'gradient_note', '', 'El degradado se genera automáticamente a partir del color principal.');
        $mform->addElement('static', 'gradient_preview', 'Vista previa del degradado', html_writer::tag('div', '', [
            'id' => 'id_brand_gradient_preview',
            'class' => 'edukav-brand-gradient-preview',
            'data-gradient' => $gradientpreview,
            'style' => 'height:56px;border-radius:18px;background:' . s($gradientpreview ?: 'linear-gradient(135deg, #f5f7fb 0%, #e6e9f5 35%, #c9c3f5 65%, #a855f7 100%)') . ';box-shadow:0 10px 24px rgba(15,23,42,.12);margin-top:8px;',
        ]));

        $mform->addElement('advcheckbox', 'visible', 'Visible');
        $mform->setType('visible', PARAM_BOOL);

        $this->add_action_buttons(true, 'Guardar partner');
    }
}
