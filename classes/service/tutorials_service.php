<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use local_edukav\repository\tutorial_categories_repository;
use local_edukav\repository\tutorials_repository;

class tutorials_service {
    public static function save(\stdClass $data): int {
        $context = context_system::instance();
        require_capability('moodle/site:config', $context);

        $title = trim((string)($data->title ?? ''));
        $description = trim((string)($data->description ?? ''));
        $url = trim((string)($data->url ?? ''));
        $categoryid = (int) ($data->categoryid ?? 0);
        if ($title === '' || $description === '' || $url === '' || $categoryid <= 0) {
            throw new \invalid_parameter_exception('Title, description, URL and category are required.');
        }
        tutorial_categories_repository::get_by_id($categoryid);

        global $CFG;
        require_once($CFG->dirroot . '/local/edukav/lib.php');
        $videoid = \local_edukav_extract_video_id($url);
        if ($videoid === null) {
            throw new \invalid_parameter_exception(get_string('tutorialinvalidurl', 'local_edukav'));
        }

        return tutorials_repository::save((object)[
            'id' => (int)($data->id ?? 0),
            'categoryid' => $categoryid,
            'title' => clean_param($title, PARAM_TEXT),
            'description' => clean_param($description, PARAM_TEXT),
            'url' => 'https://www.youtube.com/embed/' . rawurlencode($videoid),
            'visible' => empty($data->visible) ? 0 : 1,
            'sortorder' => (int) ($data->sortorder ?? 0),
        ]);
    }

    public static function delete(int $id): void {
        require_capability('moodle/site:config', context_system::instance());
        tutorials_repository::get_by_id($id);
        tutorials_repository::delete($id);
    }

    public static function get_tutorials(): array {
        global $CFG;
        require_once($CFG->dirroot . '/local/edukav/lib.php');

        $tutorials = tutorials_repository::get_visible();

        foreach ($tutorials as $tutorial) {
            $tutorial->video_id =
                \local_edukav_extract_video_id($tutorial->url);
        }

        return array_values($tutorials);
    }
}
