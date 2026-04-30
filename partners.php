<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/forms/partner_form.php');
require_once(__DIR__ . '/lib.php');

use local_edukav\forms\partner_form;
use local_edukav\service\partners_service;

require_login();
require_capability('moodle/site:config', context_system::instance());

$action = optional_param('action', '', PARAM_ALPHA);
$partnerid = optional_param('id', 0, PARAM_INT);
$context = context_system::instance();

if ($action === 'delete' && $partnerid) {
    require_sesskey();
    partners_service::delete_partner($partnerid);
    redirect(new moodle_url('/local/edukav/partners.php'), 'Partner eliminado correctamente', null, \core\output\notification::NOTIFY_SUCCESS);
}

$editing = null;
$logooptions = [
    'subdirs' => 0,
    'maxfiles' => 1,
    'accepted_types' => ['image'],
];

$formdata = new stdClass();
$partneritemid = 0;
if ($partnerid) {
    $editing = partners_service::get_partner($partnerid);
    if ($editing) {
        $formdata = clone $editing;
        $formdata->currentlogo = partners_service::get_partner_logo_url($partnerid);
        $partneritemid = $partnerid;
    }
}

$formdata->currentlogo = $formdata->currentlogo ?? '';
file_prepare_standard_filemanager(
    $formdata,
    'logo',
    $logooptions,
    $context,
    'local_edukav',
    partners_service::FILEAREA_PARTNER_LOGO,
    $partneritemid
);

$formdata->gradientpreview = partners_service::build_partner_gradient($formdata->brand_color ?? '#a855f7');
$form = new partner_form(null, ['data' => $formdata, 'currentlogo' => $formdata->currentlogo ?? '', 'gradientpreview' => $formdata->gradientpreview]);
$form->set_data($formdata);

$PAGE->requires->js_call_amd('local_edukav/brand_color_picker', 'init', [
    '#id_brand_color_text',
]);
$PAGE->requires->js_call_amd('local_edukav/brand_gradient_preview', 'init', [
    '#id_brand_color_text',
    '#id_brand_gradient_preview',
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/edukav/partners.php'));
}

if ($data = $form->get_data()) {
    require_sesskey();

    if (!empty($data->id)) {
        partners_service::update_partner(
            (int)$data->id,
            $data->name,
            $data->slug ?? '',
            '',
            $data->brand_color ?? '',
            !empty($data->visible)
        );
        local_edukav_save_partner_logo((int)$data->id, (int)($data->logo ?? 0));
        redirect(new moodle_url('/local/edukav/partners.php'), 'Partner actualizado correctamente', null, \core\output\notification::NOTIFY_SUCCESS);
    }

    $newid = partners_service::create_partner(
        $data->name,
        $data->slug ?? '',
        '',
        $data->brand_color ?? '',
        !empty($data->visible)
    );
    $data->id = $newid;
    local_edukav_save_partner_logo($newid, (int)($data->logo ?? 0));
    redirect(new moodle_url('/local/edukav/partners.php'), 'Partner creado correctamente', null, \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->set_url(new moodle_url('/local/edukav/partners.php'));
$PAGE->set_context($context);
$PAGE->set_title('Partners');
$PAGE->set_heading('Gestión de partners');

$partners = partners_service::get_all_partners(false);

echo $OUTPUT->header();
echo $OUTPUT->heading('Gestión de partners');

echo html_writer::start_tag('div', ['class' => 'card mb-4 p-5']);
echo html_writer::start_tag('div', ['class' => 'card-body']);
$form->display();
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'card']);
echo html_writer::start_tag('div', ['class' => 'card-body']);
echo html_writer::tag('h3', 'Partners existentes');

if (empty($partners)) {
    echo html_writer::tag('p', 'Todavía no hay partners registrados.');
} else {
    echo html_writer::start_tag('div', ['class' => 'table-responsive']);
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::tag('tr',
        html_writer::tag('th', 'Logo') .
        html_writer::tag('th', 'Nombre') .
        html_writer::tag('th', 'Slug') .
        html_writer::tag('th', 'Color') .
        html_writer::tag('th', 'Visible') .
        html_writer::tag('th', 'Acciones')
    );
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($partners as $partner) {
        $editurl = new moodle_url('/local/edukav/partners.php', ['id' => $partner->id]);
        $deleteurl = new moodle_url('/local/edukav/partners.php', ['action' => 'delete', 'id' => $partner->id, 'sesskey' => sesskey()]);
        $logourl = partners_service::get_partner_logo_url((int)$partner->id);

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td',
            $logourl ? html_writer::empty_tag('img', [
                'src' => $logourl,
                'alt' => s($partner->name),
                'style' => 'width:48px;height:48px;object-fit:contain;border-radius:10px;background:#fff;padding:4px;'
            ]) : '-'
        );
        echo html_writer::tag('td', s($partner->name));
        echo html_writer::tag('td', s($partner->slug));
        echo html_writer::tag('td', s($partner->brand_color ?: '-'));
        echo html_writer::tag('td', ((int)$partner->visible === 1) ? 'Sí' : 'No');
        echo html_writer::tag('td',
            html_writer::link($editurl, 'Editar', ['class' => 'btn btn-sm btn-outline-primary me-2']) .
            html_writer::link($deleteurl, 'Eliminar', ['class' => 'btn btn-sm btn-outline-danger'])
        );
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
    echo html_writer::end_tag('div');
}

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo $OUTPUT->footer();
