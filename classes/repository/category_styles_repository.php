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
 * Repository for course category presentation settings.
 *
 * @package    local_edukav
 * @copyright  2026 Edukav
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

/** Persistence for optional course category presentation settings. */
class category_styles_repository {
    public static function get_all(): array {
        global $DB;

        $fields = 'id, categoryid, displaytype, icontype, icon, imagealt, timecreated, timemodified';
        return $DB->get_records('edukav_category_styles', null, 'categoryid ASC', $fields);
    }

    public static function get_by_categoryid(int $categoryid): ?\stdClass {
        global $DB;

        $fields = 'id, categoryid, displaytype, icontype, icon, imagealt, timecreated, timemodified';
        $record = $DB->get_record('edukav_category_styles', ['categoryid' => $categoryid], $fields);
        return $record ?: null;
    }

    public static function get_by_id(int $id): ?\stdClass {
        global $DB;

        $fields = 'id, categoryid, displaytype, icontype, icon, imagealt, timecreated, timemodified';
        $record = $DB->get_record('edukav_category_styles', ['id' => $id], $fields);
        return $record ?: null;
    }

    public static function save(\stdClass $record): int {
        global $DB;

        $existing = self::get_by_categoryid((int)$record->categoryid);
        $record->timemodified = time();
        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('edukav_category_styles', $record);
            return (int)$existing->id;
        }

        unset($record->id);
        $record->timecreated = $record->timemodified;
        return (int)$DB->insert_record('edukav_category_styles', $record);
    }

    public static function delete_by_categoryid(int $categoryid): bool {
        global $DB;
        return $DB->delete_records('edukav_category_styles', ['categoryid' => $categoryid]);
    }
}
