<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use local_edukav\repository\tutorials_repository;
use required_capability_exception;

class tutorials_service {

    /**
     * Crear Tuturial
     * (público o con validaciones mínimas)
     */
    public static function create_tutorial(string $title, string $description, string $url): int {

        if (empty(trim($title)) || empty(trim($description)) || empty(trim($url))) {
            throw new \invalid_parameter_exception('Todos los campos son obligatorios');
        }

        // Formatear URL de YouTube
        $formatted_url = self::format_video_url($url);

        if (!$formatted_url) {
            throw new \invalid_parameter_exception('URL de video no válida');
        }

        $data = (object)[
            'title' => $title,
            'description' => $description,
            'url' => $formatted_url,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        return tutorials_repository::create(
            $data->title,
            $data->description,
            $data->url
        );
    }

    /**
     * Formateer URL de video (ejemplo para YouTube)
     */
    private static function format_video_url(string $url): ?string {

        $url = trim($url);

        if (empty($url)) {
            return null;
        }

        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {

            if (preg_match('/(youtu\.be\/|v=)([^&]+)/', $url, $matches)) {
                $video_id = $matches[2];
                return "https://www.youtube.com/embed/{$video_id}";
            }
        }

        return null;
    }


    /**
     * Editar Tutorial
     * (requiere permisos)
     */
    public static function update_tutorial(int $id, string $title, string $description,string $url): void {
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }

        
        if (empty(trim($title)) || empty(trim($description)) || empty(trim($url))) {
            throw new \invalid_parameter_exception('Todos los campos son obligatorias');
        }

        $data = (object)[
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'timemodified' => time(),
        ];

        tutorials_repository::update($data);
    }

    /**
     * Eliminar Tutorial
     * (requiere permisos)
     */
    public static function delete_tutorial(int $id): void {
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }

        tutorials_repository::delete($id);
    }
}
