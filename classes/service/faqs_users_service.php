<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use local_edukav\repository\faqs_users_repository;
use required_capability_exception;

class faqs_users_service {

    /**
     * Crear FAQ
     * (público o con validaciones mínimas)
     */
    public static function create_faq_user(string $name, string $email,string $question): int {
        // Reglas de negocio básicas
        if (empty(trim($name)) || empty(trim($email)) || empty(trim($question))) {
            throw new \invalid_parameter_exception('Todos los campos son obligatorias');
        }

        $data = (object)[
            'name' => $name,
            'email'   => $email,
            'question'   => $question,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        return faqs_users_repository::create(
            $data->name,
            $data->email,
            $data->question
        );
    }

    /**
     * Editar FAQ
     * (requiere permisos)
     */
    public static function update_faq_user(int $id, string $name, string $email,string $question): void {
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }

        
        if (empty(trim($name)) || empty(trim($email)) || empty(trim($question))) {
            throw new \invalid_parameter_exception('Todos los campos son obligatorias');
        }

        $data = (object)[
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'question' => $question,
            'timemodified' => time(),
        ];

        faqs_users_repository::update($data);
    }

    /**
     * Eliminar FAQ
     * (requiere permisos)
     */
    public static function delete_faq_user(int $id): void {
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }

        faqs_users_repository::delete($id);
    }
}
