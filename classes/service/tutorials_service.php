<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use local_edukav\repository\tutorials_repository;

class tutorials_service {
    public static function save(\stdClass $data): int {
        $context = context_system::instance();
        require_capability('moodle/site:config', $context);

        $title = trim((string)($data->title ?? ''));
        $description = trim((string)($data->description ?? ''));
        $url = trim((string)($data->url ?? ''));
        if ($title === '' || $description === '' || $url === '') {
            throw new \invalid_parameter_exception('Title, description and URL are required.');
        }

        global $CFG;
        require_once($CFG->dirroot . '/local/edukav/lib.php');
        $videoid = \local_edukav_extract_video_id($url);
        if ($videoid === null) {
            throw new \invalid_parameter_exception(get_string('tutorialinvalidurl', 'local_edukav'));
        }

        return tutorials_repository::save((object)[
            'id' => (int)($data->id ?? 0),
            'title' => clean_param($title, PARAM_TEXT),
            'description' => clean_param($description, PARAM_TEXT),
            'url' => 'https://www.youtube.com/embed/' . rawurlencode($videoid),
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

        $tutorials = tutorials_repository::get_all();

        foreach ($tutorials as $tutorial) {
            $tutorial->video_id =
                \local_edukav_extract_video_id($tutorial->url);
        }

        return array_values($tutorials);
    }
}
