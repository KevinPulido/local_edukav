<?php

namespace local_edukav\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use local_edukav\service\partners_service;

class get_course_partner extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id')
        ]);
    }

    public static function execute($courseid) {

        $data = partners_service::get_course_partner_branding_from_db((int)$courseid);

        if (!$data) {
            return [
                'found' => 0,
                'name' => '',
                'logo' => '',
                'brand_color' => ''
            ];
        }

        return [
            'found' => 1,
            'name' => $data['name'],
            'logo' => $data['logo'],
            'brand_color' => $data['brand_color']
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'found' => new external_value(PARAM_INT, 'Found'),
            'name' => new external_value(PARAM_TEXT, 'Name'),
            'logo' => new external_value(PARAM_URL, 'Logo'),
            'brand_color' => new external_value(PARAM_TEXT, 'Color')
        ]);
    }
}