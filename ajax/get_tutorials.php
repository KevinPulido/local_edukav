<?php
require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

if (!has_capability('moodle/site:config', $context)) {
    throw new required_capability_exception($context, 'moodle/site:config', 'nopermissions', '');
}

$id = required_param('id', PARAM_INT);

$tutorial = \local_edukav\repository\tutorials_repository::get_by_id($id);

header('Content-Type: application/json');
echo json_encode([
    'id' => $tutorial->id,
    'title' => $tutorial->title,
    'description' => $tutorial->description,
    'url' => $tutorial->url,
]);
