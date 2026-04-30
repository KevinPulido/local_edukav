<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use core_text;
use moodle_url;
use local_edukav\repository\partners_repository;
use required_capability_exception;

class partners_service {
    public const FILEAREA_PARTNER_LOGO = 'partner_logo';

    private static function require_admin_capability(): void {
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }
    }

    private static function normalize_slug(string $name, ?string $slug = null): string {
        $source = trim($slug ?? '');
        if ($source === '') {
            $source = trim($name);
        }

        $source = core_text::strtolower($source);
        $source = preg_replace('/[^a-z0-9]+/u', '-', $source);
        $source = trim((string)$source, '-');

        return $source !== '' ? $source : 'partner';
    }

    private static function normalize_color(?string $color): string {
        $color = trim((string)$color);
        if ($color === '') {
            return '';
        }

        if ($color[0] !== '#') {
            $color = '#' . $color;
        }

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return '';
        }

        return strtolower($color);
    }

    public static function build_partner_gradient(?string $brandcolor): string {
        $brandcolor = self::normalize_color($brandcolor);
        $endcolor = $brandcolor !== '' ? $brandcolor : '#a855f7';

        return "linear-gradient(135deg, #f5f7fb 0%, #e6e9f5 35%, #c9c3f5 65%, {$endcolor} 100%)";
    }

    private static function unique_slug(string $base, ?int $excludeid = null): string {
        $slug = $base;
        $counter = 2;

        while ($existing = partners_repository::get_by_slug($slug)) {
            if ($excludeid !== null && (int)$existing->id === $excludeid) {
                break;
            }

            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public static function get_all_partners(bool $onlyvisible = false): array {
        return partners_repository::get_all($onlyvisible);
    }

    public static function get_partner(int $id): ?\stdClass {
        return partners_repository::get_by_id($id);
    }

    public static function get_partner_logo_url(int $partnerid): string {
        if ($partnerid <= 0) {
            return '';
        }

        $context = context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id,
            'local_edukav',
            self::FILEAREA_PARTNER_LOGO,
            $partnerid,
            'itemid, filepath, filename',
            false
        );

        if (!empty($files)) {
            $file = reset($files);
            if ($file) {
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

        return '';
    }

    public static function get_partner_options(bool $onlyvisible = false): array {
        $options = [];

        foreach (self::get_all_partners($onlyvisible) as $partner) {
            $options[(int)$partner->id] = $partner->name;
        }

        return $options;
    }

    public static function create_partner(string $name, ?string $slug = null, ?string $logo = '', ?string $brandcolor = '', bool $visible = true): int {
        self::require_admin_capability();

        $name = trim($name);
        if ($name === '') {
            throw new \invalid_parameter_exception('El nombre del partner es obligatorio');
        }

        $normalizedslug = self::unique_slug(self::normalize_slug($name, $slug));
        $record = (object)[
            'name' => $name,
            'slug' => $normalizedslug,
            'logo' => '',
            'brand_color' => self::normalize_color($brandcolor),
            'visible' => $visible ? 1 : 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        return partners_repository::create($record);
    }

    public static function update_partner(int $id, string $name, ?string $slug = null, ?string $logo = '', ?string $brandcolor = '', bool $visible = true): void {
        self::require_admin_capability();

        $record = partners_repository::get_by_id($id);
        if (!$record) {
            throw new \invalid_parameter_exception('Partner no encontrado');
        }

        $name = trim($name);
        if ($name === '') {
            throw new \invalid_parameter_exception('El nombre del partner es obligatorio');
        }

        $record->name = $name;
        $record->slug = self::unique_slug(self::normalize_slug($name, $slug), $id);
        $record->logo = '';
        $record->brand_color = self::normalize_color($brandcolor);
        $record->visible = $visible ? 1 : 0;
        $record->timemodified = time();

        partners_repository::update($record);
    }

    public static function delete_partner(int $id): void {
        self::require_admin_capability();
        partners_repository::delete($id);
    }

    public static function get_course_partner_branding(int $courseid): array {
        if (!function_exists('course_get_format')) {
            return [];
        }

        try {
            $format = course_get_format($courseid);
            $partnerid = (int)$format->get_format_option('partnerid');
            if ($partnerid <= 0) {
                return [];
            }

            $partner = self::get_partner($partnerid);
            if (!$partner) {
                return [];
            }

            return [
                'id' => (int)$partner->id,
                'name' => (string)$partner->name,
                'slug' => (string)$partner->slug,
                'logo' => self::get_partner_logo_url((int)$partner->id),
                'brand_color' => trim((string)$partner->brand_color),
                'gradient' => self::build_partner_gradient((string)$partner->brand_color),
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
