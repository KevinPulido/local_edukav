<?php
require_once(__DIR__ . '/../../../../../config.php');

use local_edukav\service\tutorials_service;

require_login();
require_sesskey();

$id       = required_param('tutorial_id', PARAM_INT);
$title = required_param('title', PARAM_TEXT);
$description   = required_param('description', PARAM_TEXT);
$url   = required_param('video_url', PARAM_TEXT);


// Llamada al service
tutorials_service::update_tutorial($id, $title, $description, $url);

// Redirección
redirect(
    new moodle_url('/theme/edukav/layout/tutorials.php'),
    '✏️ FAQ actualizada correctamente'
);
