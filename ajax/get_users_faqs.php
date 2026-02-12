<?php
require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

if (!has_capability('moodle/site:config', $context)) {
    throw new required_capability_exception($context, 'moodle/site:config', 'nopermissions', '');
}

$id = required_param('id', PARAM_INT);

$faq_user_user = \local_edukav\repository\faqs_users_repository::get_by_id($id);

header('Content-Type: application/json');
echo json_encode([
    'id' => $faq_user->id,
    'name' => $faq_user->name,
    'email' => $faq_user->email,
    'question' => $faq_user->question,
]);
