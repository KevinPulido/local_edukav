<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use local_edukav\repository\tutorial_categories_repository;

class tutorial_categories_service {
    public static function save(\stdClass $data): int {
        require_capability('moodle/site:config', context_system::instance());

        $name = trim((string) ($data->name ?? ''));
        if ($name === '') {
            throw new \invalid_parameter_exception('Category name is required.');
        }

        $base = clean_param(\core_text::strtolower($name), PARAM_ALPHANUMEXT);
        $base = trim(str_replace(' ', '-', $base), '-');
        if ($base === '') {
            $base = 'category';
        }
        $slug = $base;
        $suffix = 2;
        $id = (int) ($data->id ?? 0);
        while (tutorial_categories_repository::slug_exists($slug, $id)) {
            $slug = $base . '-' . $suffix++;
        }

        return tutorial_categories_repository::save((object) [
            'id' => $id,
            'name' => clean_param($name, PARAM_TEXT),
            'slug' => $slug,
            'icon' => clean_param((string) ($data->icon ?? 'camera-video'), PARAM_ALPHANUMEXT),
            'visible' => empty($data->visible) ? 0 : 1,
            'sortorder' => (int) ($data->sortorder ?? 0),
        ]);
    }

    public static function delete(int $id): void {
        require_capability('moodle/site:config', context_system::instance());
        tutorial_categories_repository::get_by_id($id);
        if (tutorial_categories_repository::has_tutorials($id)) {
            throw new \moodle_exception('tutorialcategoryinuse', 'local_edukav');
        }
        tutorial_categories_repository::delete($id);
    }
}
