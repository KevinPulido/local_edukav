<?php
require_once(__DIR__ . '/../../../../../config.php');

use local_edukav\service\faqs_users_service;

require_login();
require_sesskey();

$name = required_param('name', PARAM_TEXT);
$email   = required_param('email', PARAM_TEXT);
$question   = required_param('question', PARAM_TEXT);

// Llamada al service
faqs_users_service::create_faq_user($name, $email,$question );

// Redirección
redirect(
    new moodle_url('/theme/edukav/layout/faqs.php'),
    '✅ FAQ creada correctamente'
);
