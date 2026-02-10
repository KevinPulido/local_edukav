<?php
require_once(__DIR__ . '/../../../../../config.php');

use local_edukav\service\faqs_service;

require_login();
require_sesskey();

$question = required_param('question', PARAM_TEXT);
$answer   = required_param('answer', PARAM_TEXT);

// Llamada al service
faqs_service::create_faq($question, $answer);

// Redirección
redirect(
    new moodle_url('/theme/edukav/layout/faqs.php'),
    '✅ FAQ creada correctamente'
);
