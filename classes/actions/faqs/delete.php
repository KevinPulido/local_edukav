<?php
require_once(__DIR__ . '/../../../../../config.php');

use local_edukav\service\faqs_service;

require_login();
require_sesskey();

$id = required_param('id', PARAM_INT);

// Llamada al service
faqs_service::delete_faq($id);

// Redirección
redirect(
    new moodle_url('/theme/edukav/layout/faqs.php'),
    '🗑️ FAQ eliminada correctamente'
);
