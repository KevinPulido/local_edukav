<?php
require_once(__DIR__ . '/../../../../../config.php');

use local_edukav\service\tutorials_service;

require_login();
require_sesskey();

$id = required_param('tutorial_id', PARAM_INT);

// Llamada al service
tutorials_service::delete_tutorial($id);

// Redirección
redirect(
    new moodle_url('/theme/edukav/layout/tutorials.php'),
    '🗑️ FAQ eliminada correctamente'
);
