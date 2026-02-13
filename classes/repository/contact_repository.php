<?php
namespace local_edukav\repository;

defined('MOODLE_INTERNAL') || die();

class contact_repository{

    public static function get_all():array{
        global $DB;
        return $DB->get_records('edukav_contact', null, 'timecreated DESC');
    }

    public static function get_by_id(int $id){
        global $DB;
        return $DB->get_record('edukav_contact', ['id' => $id], '*', MUST_EXIST);
    }

    public static function create(string $name,string $email,string $subject,string $message):int{
        global $DB;

        $record = new \stdClass();
        $record->name = $name;
        $record->email = $email;
        $record->subject = $subject;
        $record->message = $message;
        $record->timecreated = time();
        $record->timemodified = time();
        return $DB->insert_record('edukav_contacts',$record);
    }

    public static function update(\stdClass $record):bool{
        global $DB;

        return $DB->update_record('edukav_contacts',$record);
    }

    public static function delecte(int $id):bool{
        global $DB;
        return $DB->delete_records('edukav_contacts',['id' => $id]);
    }


}