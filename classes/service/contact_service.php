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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   Contact service
 * @copyright 2026, Kevin Pulido
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();


use context_system;
use local_edukav\repository\contact_repository;
use required_capability_exception;

class contact_service{
    /**
     * Create Contact
     */
    public static function create_contact(string $name,string $email,string $subject, string $message):int{
        if(empty(trim($name)) || empty(trim($email)) || empty(trim($subject)) || empty(trim($message))){
            throw new \invalid_parameter_exception('Todos los campos son obligatorios');
        }

        $data = (object)[
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        
        return contact_repository::create(
            $data->name,
            $data->email,
            $data->subject,
            $data->message
        );
    }

    /**
     * Edit Contact
     */
    public static function edit_contact():void{
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }

        if(empty(trim($name)) || empty(trim($email)) || empty(trim($subject)) || empty(trim($message))){
            throw new \invalid_parameter_exception('Todos los campos son obligatorios');
        }

        $data = (object)[
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'timemodified' => time(),
        ];

        contact_repository::update($data);
    }

    /**
     * Delete Contact
     */
    public static function delete_contact(int $id):void{
        $context = context_system::instance();

        if (!has_capability('moodle/site:config', $context)) {
            throw new required_capability_exception(
                $context,
                'moodle/site:config',
                'nopermissions',
                ''
            );
        }

        contact_repository::delecte($id);
    }
}
