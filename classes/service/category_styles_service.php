<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Service for course category presentation settings.
 *
 * @package    local_edukav
 * @copyright  2026 Edukav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use local_edukav\repository\category_styles_repository;
use moodle_url;

/** Manages the optional presentation assigned to Moodle course categories. */
class category_styles_service {
    public const FILEAREA_CATEGORY_IMAGE = 'category_image';
    public const FILEAREA_CATEGORY_ICON = 'category_icon';

    private const ICONS = [
        'bi-book' => 'Libro',
        'bi-code-slash' => 'Programación',
        'bi-laptop' => 'Tecnología',
        'bi-briefcase' => 'Negocios',
        'bi-palette' => 'Diseño',
        'bi-translate' => 'Idiomas',
        'bi-people' => 'Personas',
        'bi-person-workspace' => 'Formación',
        'bi-lightbulb' => 'Ideas',
        'bi-graph-up-arrow' => 'Crecimiento',
        'bi-heart-pulse' => 'Bienestar',
        'bi-stars' => 'Destacado',
    ];

    public static function get_icon_options(): array {
        return self::ICONS;
    }

    public static function save(\stdClass $data): int {
        global $DB;

        require_capability('moodle/site:config', context_system::instance());
        $categoryid = (int)($data->categoryid ?? 0);
        if ($categoryid <= 0 || !$DB->record_exists('course_categories', ['id' => $categoryid])) {
            throw new \invalid_parameter_exception('Invalid course category.');
        }

        $draftitemid = (int)($data->categoryimage ?? 0);
        $hasimage = $draftitemid > 0 && !empty(file_get_all_files_in_draftarea($draftitemid));
        $displaytype = $hasimage ? 'image' : 'icon';
        $icontype = (string)($data->icontype ?? 'bootstrap');
        if (!in_array($icontype, ['bootstrap', 'image'], true)) {
            $icontype = 'bootstrap';
        }
        $icon = (string)($data->icon ?? 'bi-book');
        if (!array_key_exists($icon, self::ICONS)) {
            $icon = 'bi-book';
        }

        $styleid = category_styles_repository::save((object)[
            'categoryid' => $categoryid,
            'displaytype' => $displaytype,
            'icontype' => $icontype,
            'icon' => $icon,
            'imagealt' => clean_param(trim((string)($data->imagealt ?? '')), PARAM_TEXT),
        ]);

        $context = context_system::instance();
        file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'local_edukav',
            self::FILEAREA_CATEGORY_IMAGE,
            $styleid,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['image']]
        );
        file_save_draft_area_files(
            (int)($data->categoryiconimage ?? 0),
            $context->id,
            'local_edukav',
            self::FILEAREA_CATEGORY_ICON,
            $styleid,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['image']]
        );

        frontpage_service::purge_statistics_cache();
        return $styleid;
    }

    public static function delete_by_categoryid(int $categoryid): void {
        $record = category_styles_repository::get_by_categoryid($categoryid);
        if (!$record) {
            return;
        }

        $context = context_system::instance();
        get_file_storage()->delete_area_files(
            $context->id,
            'local_edukav',
            self::FILEAREA_CATEGORY_IMAGE,
            (int)$record->id
        );
        get_file_storage()->delete_area_files(
            $context->id,
            'local_edukav',
            self::FILEAREA_CATEGORY_ICON,
            (int)$record->id
        );
        category_styles_repository::delete_by_categoryid($categoryid);
        frontpage_service::purge_statistics_cache();
    }

    public static function reset(int $categoryid): void {
        require_capability('moodle/site:config', context_system::instance());
        self::delete_by_categoryid($categoryid);
    }

    public static function get_image_url(int $styleid): string {
        return self::get_file_url($styleid, self::FILEAREA_CATEGORY_IMAGE);
    }

    public static function get_icon_image_url(int $styleid): string {
        return self::get_file_url($styleid, self::FILEAREA_CATEGORY_ICON);
    }

    private static function get_file_url(int $styleid, string $filearea): string {
        if ($styleid <= 0) {
            return '';
        }

        $context = context_system::instance();
        $files = get_file_storage()->get_area_files(
            $context->id,
            'local_edukav',
            $filearea,
            $styleid,
            'itemid, filepath, filename',
            false
        );
        if (!$files) {
            return '';
        }

        $file = reset($files);
        return moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }
}
