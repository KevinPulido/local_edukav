<?php
require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

if (!has_capability('moodle/site:config', $context)) {
    throw new required_capability_exception($context, 'moodle/site:config', 'nopermissions', '');
}

$id = required_param('id', PARAM_INT);

$faq = \local_edukav\repository\faqs_repository::get_by_id($id);

header('Content-Type: application/json');
echo json_encode([
    'id' => $faq->id,
    'question' => $faq->question,
    'answer' => $faq->answer,
]);
