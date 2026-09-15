<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_course;
use core_text;
use local_edukav\repository\course_library_repository;
use moodle_url;
use required_capability_exception;

/** Business rules and template data for each course library. */
class course_library_service {
    public const FILEAREA_RESOURCE = 'course_library_resource';
    public const FILEAREA_COVER = 'course_library_cover';
    public const CAPABILITY_MANAGE = 'local/edukav:managecourselibrary';

    private const TYPES = ['book', 'video', 'pdf', 'audio', 'document', 'link'];
    private const LEVELS = ['basic', 'intermediate', 'advanced'];

    public static function get_type_options(): array {
        $options = [];
        foreach (self::TYPES as $type) {
            $options[$type] = get_string('resourcetype_' . $type, 'local_edukav');
        }
        return $options;
    }

    public static function get_level_options(): array {
        $options = [];
        foreach (self::LEVELS as $level) {
            $options[$level] = get_string('resourcelevel_' . $level, 'local_edukav');
        }
        return $options;
    }

    public static function can_manage(int $courseid): bool {
        return has_capability(self::CAPABILITY_MANAGE, context_course::instance($courseid));
    }

    private static function require_manage(int $courseid): void {
        $context = context_course::instance($courseid);
        if (!has_capability(self::CAPABILITY_MANAGE, $context)) {
            throw new required_capability_exception($context, self::CAPABILITY_MANAGE, 'nopermissions', '');
        }
    }

    public static function get_resource(int $id, int $courseid): ?\stdClass {
        $resource = course_library_repository::get_by_id($id);
        return $resource && (int)$resource->courseid === $courseid ? $resource : null;
    }

    public static function get_records_for_management(int $courseid): array {
        self::require_manage($courseid);
        return course_library_repository::get_by_course($courseid, false);
    }

    public static function get_library_context(int $courseid): array {
        $context = context_course::instance($courseid);
        $canmanage = self::can_manage($courseid);
        $records = course_library_repository::get_by_course($courseid, !$canmanage);
        $types = self::get_type_options();
        $levels = self::get_level_options();
        $resources = [];
        $typecounts = [];
        $levelcounts = [];
        $categorycounts = [];
        $featured = false;

        foreach ($records as $record) {
            $item = self::export_resource($record, $context, $types, $levels, $canmanage);
            $resources[] = $item;
            $typecounts[$item['type']] = ($typecounts[$item['type']] ?? 0) + 1;
            $levelcounts[$item['level']] = ($levelcounts[$item['level']] ?? 0) + 1;
            $categorycounts[$item['categorykey']] = [
                'value' => $item['categorykey'],
                'label' => $item['category'],
                'count' => ($categorycounts[$item['categorykey']]['count'] ?? 0) + 1,
            ];
            if ($featured === false && $item['featured'] && $item['visible']) {
                $featured = $item;
            }
        }

        $typefilters = [];
        foreach ($types as $value => $label) {
            if (!empty($typecounts[$value])) {
                $typefilters[] = [
                    'value' => $value,
                    'label' => $label,
                    'count' => $typecounts[$value],
                    'icon' => self::get_type_icon($value),
                ];
            }
        }
        $levelfilters = [];
        foreach ($levels as $value => $label) {
            if (!empty($levelcounts[$value])) {
                $levelfilters[] = ['value' => $value, 'label' => $label, 'count' => $levelcounts[$value]];
            }
        }
        uasort($categorycounts, static fn(array $a, array $b): int => strcasecmp($a['label'], $b['label']));

        return [
            'courseid' => $courseid,
            'resources' => $resources,
            'hasresources' => !empty($resources),
            'resourcecount' => count($resources),
            'categorycount' => count($categorycounts),
            'typecount' => count($typefilters),
            'featuredresource' => $featured,
            'hasfeatured' => $featured !== false,
            'categoryfilters' => array_values($categorycounts),
            'hascategories' => !empty($categorycounts),
            'typefilters' => $typefilters,
            'hastypes' => !empty($typefilters),
            'levelfilters' => $levelfilters,
            'haslevels' => !empty($levelfilters),
            'canmanage' => $canmanage,
            'libraryurl' => (new moodle_url('/local/edukav/library.php', ['courseid' => $courseid]))->out(false),
            'manageurl' => (new moodle_url('/local/edukav/course_library.php', ['courseid' => $courseid]))->out(false),
        ];
    }

    private static function export_resource(
        \stdClass $record,
        context_course $context,
        array $types,
        array $levels,
        bool $canmanage
    ): array {
        $resourceurl = self::get_file_url($context, self::FILEAREA_RESOURCE, (int)$record->id);
        if ($resourceurl === '') {
            $resourceurl = clean_param((string)$record->externalurl, PARAM_URL);
        }
        $coverurl = self::get_file_url($context, self::FILEAREA_COVER, (int)$record->id);
        $category = trim((string)$record->category) ?: get_string('category_general', 'local_edukav');
        $type = array_key_exists($record->resourcetype, $types) ? $record->resourcetype : 'document';
        $level = array_key_exists($record->level, $levels) ? $record->level : 'basic';
        $descriptiontext = trim(strip_tags((string)$record->description));

        return [
            'id' => (int)$record->id,
            'title' => format_string($record->title, true, ['context' => $context]),
            'description' => $descriptiontext,
            'category' => format_string($category, true, ['context' => $context]),
            'categorykey' => self::normalize_key($category),
            'type' => $type,
            'typelabel' => $types[$type],
            'typeicon' => self::get_type_icon($type),
            'isbook' => $type === 'book',
            'isvideo' => $type === 'video',
            'ispdf' => $type === 'pdf',
            'isaudio' => $type === 'audio',
            'isdocument' => $type === 'document',
            'islink' => $type === 'link',
            'level' => $level,
            'levellabel' => $levels[$level],
            'url' => $resourceurl,
            'hasurl' => $resourceurl !== '',
            'coverurl' => $coverurl,
            'hascover' => $coverurl !== '',
            'featured' => !empty($record->featured),
            'visible' => !empty($record->visible),
            'hidden' => empty($record->visible),
            'searchtext' => core_text::strtolower($record->title . ' ' . $descriptiontext . ' ' . $category . ' ' . $types[$type]),
            'editurl' => $canmanage ? (new moodle_url('/local/edukav/course_library.php', [
                'courseid' => $record->courseid,
                'id' => $record->id,
            ]))->out(false) : '',
        ];
    }

    private static function get_file_url(context_course $context, string $filearea, int $itemid): string {
        $files = get_file_storage()->get_area_files(
            $context->id,
            'local_edukav',
            $filearea,
            $itemid,
            'filename, filepath',
            false
        );
        $file = $files ? reset($files) : false;
        if (!$file) {
            return '';
        }
        return moodle_url::make_pluginfile_url(
            $context->id,
            'local_edukav',
            $filearea,
            $itemid,
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }

    private static function normalize_key(string $value): string {
        $value = core_text::strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/u', '-', $value);
        return trim((string)$value, '-') ?: 'general';
    }

    private static function get_type_icon(string $type): string {
        return [
            'book' => 'bi bi-book',
            'video' => 'bi bi-play-circle',
            'pdf' => 'bi bi-file-earmark-pdf',
            'audio' => 'bi bi-headphones',
            'document' => 'bi bi-file-earmark-text',
            'link' => 'bi bi-link-45deg',
        ][$type] ?? 'bi bi-file-earmark';
    }

    public static function save_resource(int $courseid, \stdClass $data): int {
        global $USER;
        self::require_manage($courseid);

        $types = self::get_type_options();
        $levels = self::get_level_options();
        $id = (int)($data->id ?? 0);
        $existing = $id ? self::get_resource($id, $courseid) : null;
        if ($id && !$existing) {
            throw new \invalid_parameter_exception(get_string('resource_not_found', 'local_edukav'));
        }
        $title = trim((string)$data->title);
        if ($title === '') {
            throw new \invalid_parameter_exception(get_string('resource_title_required', 'local_edukav'));
        }
        $externalurl = trim((string)($data->externalurl ?? ''));
        $cleanurl = clean_param($externalurl, PARAM_URL);
        $draftitemid = (int)($data->resourcefile ?? 0);
        $hasdraftfile = $draftitemid > 0 && !empty(file_get_all_files_in_draftarea($draftitemid));
        $hasstoredfile = $existing && self::get_file_url(
            context_course::instance($courseid),
            self::FILEAREA_RESOURCE,
            $id
        ) !== '';
        $hasresourcefile = $draftitemid > 0 ? $hasdraftfile : $hasstoredfile;
        if ($cleanurl === '' && !$hasresourcefile) {
            throw new \invalid_parameter_exception(get_string('resource_source_required', 'local_edukav'));
        }
        $type = array_key_exists($data->resourcetype, $types) ? $data->resourcetype : 'document';
        $level = array_key_exists($data->level, $levels) ? $data->level : 'basic';
        $now = time();
        $record = (object)[
            'courseid' => $courseid,
            'title' => $title,
            'description' => trim((string)($data->description ?? '')),
            'category' => trim((string)($data->category ?? '')),
            'resourcetype' => $type,
            'level' => $level,
            'externalurl' => $cleanurl,
            'featured' => empty($data->featured) ? 0 : 1,
            'visible' => empty($data->visible) ? 0 : 1,
            'sortorder' => !empty($data->sortorder)
                ? (int)$data->sortorder
                : course_library_repository::get_next_sortorder($courseid),
            'modifiedby' => (int)$USER->id,
            'timemodified' => $now,
        ];
        if ($existing) {
            $record->id = $id;
            course_library_repository::update($record);
        } else {
            $record->createdby = (int)$USER->id;
            $record->timecreated = $now;
            $id = course_library_repository::create($record);
        }
        if (!empty($record->featured)) {
            course_library_repository::clear_featured($courseid, $id);
        }
        self::save_draft_files($courseid, $id, self::FILEAREA_RESOURCE, (int)($data->resourcefile ?? 0), ['*']);
        self::save_draft_files($courseid, $id, self::FILEAREA_COVER, (int)($data->coverimage ?? 0), ['image']);
        return $id;
    }

    private static function save_draft_files(
        int $courseid,
        int $itemid,
        string $filearea,
        int $draftitemid,
        array $acceptedtypes
    ): void {
        if ($draftitemid <= 0) {
            return;
        }
        file_save_draft_area_files(
            $draftitemid,
            context_course::instance($courseid)->id,
            'local_edukav',
            $filearea,
            $itemid,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => $acceptedtypes]
        );
    }

    public static function delete_resource(int $id, int $courseid): void {
        self::require_manage($courseid);
        $resource = self::get_resource($id, $courseid);
        if (!$resource) {
            throw new \invalid_parameter_exception(get_string('resource_not_found', 'local_edukav'));
        }
        $context = context_course::instance($courseid);
        get_file_storage()->delete_area_files($context->id, 'local_edukav', self::FILEAREA_RESOURCE, $id);
        get_file_storage()->delete_area_files($context->id, 'local_edukav', self::FILEAREA_COVER, $id);
        course_library_repository::delete($id);
    }

    public static function delete_course_resources(int $courseid, int $contextid): void {
        get_file_storage()->delete_area_files($contextid, 'local_edukav', self::FILEAREA_RESOURCE);
        get_file_storage()->delete_area_files($contextid, 'local_edukav', self::FILEAREA_COVER);
        course_library_repository::delete_by_course($courseid);
    }
}
