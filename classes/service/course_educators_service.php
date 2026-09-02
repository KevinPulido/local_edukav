<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_course;
use context_user;
use moodle_url;

/** Provides the educators assigned to a course. */
class course_educators_service {

    /**
     * Return educators with their public profile data and avatar URL.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_course_educators(int $courseid): array {
        global $DB, $CFG;

        $educators = [];
        try {
            $context = context_course::instance($courseid);
            $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
            if (!$role) {
                return $educators;
            }

            foreach (get_role_users($role->id, $context) as $educator) {
                $usercontext = context_user::instance($educator->id);
                $profileimageurl = moodle_url::make_pluginfile_url(
                    $usercontext->id, 'user', 'icon', null, '/', 'f1'
                );
                $educators[] = [
                    'id' => (int) $educator->id,
                    'name' => fullname($educator),
                    'profileurl' => (new moodle_url('/user/profile.php', ['id' => $educator->id]))->out(false),
                    'image' => $educator->picture
                        ? $profileimageurl->out(false)
                        : $CFG->wwwroot . '/pix/u/f1.png',
                ];
            }
        } catch (\Exception $exception) {
            debugging('Unable to load course educators: ' . $exception->getMessage(), DEBUG_DEVELOPER);
        }
        return $educators;
    }
}
