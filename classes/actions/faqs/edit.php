<?php
require_once(__DIR__ . '/../../../../config.php');

use local_edukav\service\faqs_service;

require_login();
require_sesskey();

$id       = required_param('id', PARAM_INT);
$question = required_param('question', PARAM_TEXT);
$answer   = required_param('answer', PARAM_TEXT);

// Llamada al service
faqs_service::update_faq($id, $question, $answer);

// Redirección
redirect(
    new moodle_url('/theme/edukav/layout/faqs.php'),
    '✏️ FAQ actualizada correctamente'
);
