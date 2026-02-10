<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_system;
use local_edukav\repository\faqs_repository;
use required_capability_exception;

class faqs_service {

    /**
     * Crear FAQ
     * (público o con validaciones mínimas)
     */
    public static function create_faq(string $question, string $answer): int {
        // Reglas de negocio básicas
        if (empty(trim($question)) || empty(trim($answer))) {
            throw new \invalid_parameter_exception('Pregunta y respuesta son obligatorias');
        }

        $data = (object)[
            'question' => $question,
            'answer'   => $answer,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        return faqs_repository::create(
            $data->question,
            $data->answer
        );
    }

    /**
     * Editar FAQ
     * (requiere permisos)
     */
    public static function update_faq(int $id, string $question, string $answer): void {
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }

        if (empty(trim($question)) || empty(trim($answer))) {
            throw new \invalid_parameter_exception('Pregunta y respuesta son obligatorias');
        }

        $data = (object)[
            'id' => $id,
            'question' => $question,
            'answer' => $answer,
            'timemodified' => time(),
        ];

        faqs_repository::update($data);
    }

    /**
     * Eliminar FAQ
     * (requiere permisos)
     */
    public static function delete_faq(int $id): void {
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }

        faqs_repository::delete($id);
    }
}
