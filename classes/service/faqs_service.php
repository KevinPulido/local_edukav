<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use local_edukav\repository\faq_categories_repository;
use local_edukav\repository\faqs_repository;

class faqs_service {
    public static function save(\stdClass $data): int {
        require_capability('moodle/site:config', context_system::instance());
        $question = trim((string) ($data->question ?? ''));
        $answer = trim((string) ($data->answer ?? ''));
        $categoryid = (int) ($data->categoryid ?? 0);
        if ($question === '' || $answer === '' || $categoryid <= 0) {
            throw new \invalid_parameter_exception('Question, answer and category are required.');
        }
        faq_categories_repository::get_by_id($categoryid);
        return faqs_repository::save((object) [
            'id' => (int) ($data->id ?? 0),
            'categoryid' => $categoryid,
            'question' => clean_param($question, PARAM_TEXT),
            'answer' => clean_param($answer, PARAM_TEXT),
            'visible' => empty($data->visible) ? 0 : 1,
            'sortorder' => (int) ($data->sortorder ?? 0),
        ]);
    }

    public static function delete(int $id): void {
        require_capability('moodle/site:config', context_system::instance());
        faqs_repository::get_by_id($id);
        faqs_repository::delete($id);
    }
}
