<?php
namespace local_edukav\service;

defined('MOODLE_INTERNAL') || die();

use context_course;
use core_course_list_element;
use moodle_url;

/**
 * Provides shared course card data and the public course detail.
 */
class course_detail_service {


    /** Return shared card data without loading the course syllabus. */
    public static function get_course_card(int $courseid): array {
        global $CFG, $DB, $USER, $OUTPUT;

        require_once($CFG->dirroot . '/course/lib.php');
        $course = get_course($courseid);
        $context = context_course::instance($courseid);
        $courseformat = course_get_format($courseid);
        $duration = '';
        $level = '';
        if (method_exists($courseformat, 'get_format_option')) {
            $duration = trim((string) $courseformat->get_format_option('duration'));
            $level = trim((string) $courseformat->get_format_option('level'));
        }
        if ($level === 'nolevelrequired') {
            $level = '';
        }
        if ($level !== '' && get_string_manager()->string_exists('level:' . $level, 'format_edukav')) {
            $level = get_string('level:' . $level, 'format_edukav');
        }
        $courseelement = new core_course_list_element($course);
        $imageurl = '';
        foreach ($courseelement->get_course_overviewfiles() as $file) {
            if ($file->is_valid_image()) {
                $imageurl = moodle_url::make_file_url(
                    "$CFG->wwwroot/pluginfile.php",
                    '/' . $file->get_contextid() . '/course/overviewfiles' .
                        $file->get_filepath() . $file->get_filename()
                )->out(false);
                break;
            }
        }

        $category = '';
        if ($course->category) {
            $categoryobject = \core_course_category::get($course->category, IGNORE_MISSING);
            if ($categoryobject) {
                $category = $categoryobject->get_formatted_name();
            }
        }

        if ($imageurl === '') {
            $imageurl = $OUTPUT->get_generated_image_for_id($courseid);
        }
        $paymentmethods = $DB->get_records_sql(
            "SELECT e.id, e.cost
               FROM {enrol} e
              WHERE e.courseid = :courseid
                AND e.enrol = 'edukav_payments'
                AND e.status = 0
           ORDER BY e.sortorder ASC, e.id ASC",
            ['courseid' => $course->id], 0, 1
        );
        $paymentmethod = reset($paymentmethods);
        $price = $paymentmethod ? (float) $paymentmethod->cost : 0.0;
        $originalprice = $price > 0 ? round($price * 1.49, 2) : 0;
        $discountpercent = $price > 0 && $originalprice > $price
            ? (int) round((1 - ($price / $originalprice)) * 100)
            : 0;

        return [
            'id' => $courseid,
            'fullname' => format_string($course->fullname, true, ['context' => $context]),
            'visible' => $course->visible,
            'image' => $imageurl,
            'category' => $category,
            'startdate' => date('d M, Y', (int) $course->startdate),
            'duration' => $duration,
            'level' => $level,
            'hasduration' => $duration !== '',
            'haslevel' => $level !== '',
            'price' => $price,
            'isfree' => $price <= 0,
            'originalprice' => $originalprice,
            'discountpercent' => $discountpercent,
            'isenrolled' => is_enrolled($context, $USER),
        ];
    }

    /**
     * Return the data that can be displayed before enrolment.
     *
     * @param int $courseid
     * @return array
     * @throws \moodle_exception
     */
    public static function get_course_detail(int $courseid): array {
        global $CFG;

        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/local/edukav/classes/service/course_educators_service.php');

        $course = get_course($courseid);
        if ((int) $course->id === SITEID) {
            throw new \moodle_exception('invalidcourseid');
        }

        $context = context_course::instance($course->id);
        $courseformat = course_get_format($course->id);
        $card = self::get_course_card($courseid);
        $videotype = (string) $courseformat->get_format_option('banner_video_type');
        $videourl = null;
        $isvideofile = false;
        if ($videotype === 'upload') {
            $videofile = self::get_uploaded_banner_video($course->id);
            if ($videofile) {
                $videourl = moodle_url::make_pluginfile_url(
                    $context->id,
                    'format_edukav',
                    'bannervideo',
                    0,
                    $videofile->get_filepath(),
                    $videofile->get_filename()
                )->out(false);
                $isvideofile = true;
            }
        } else {
            $bannervideo = trim((string) $courseformat->get_format_option('banner_video'));
            $videourl = $bannervideo !== '' && method_exists($courseformat, 'normalize_video_url')
                ? $courseformat->normalize_video_url($bannervideo)
                : null;
        }
        $educators = course_educators_service::get_course_educators($course->id);
        $teacher = reset($educators);

        $sections = [];
        $modinfo = get_fast_modinfo($course->id);
        foreach ($modinfo->get_section_info_all() as $section) {
            if ((int) $section->section === 0 || !$section->visible) {
                continue;
            }

            $activities = [];
            foreach ($modinfo->get_cms() as $cm) {
                if ((int) $cm->sectionnum !== (int) $section->section || !$cm->visibleoncoursepage) {
                    continue;
                }

                $activities[] = [
                    'name' => $cm->get_formatted_name(),
                    'modname' => get_string('modulename', 'mod_' . $cm->modname),
                    'url' => isloggedin() && $cm->uservisible ? $cm->url->out(false) : '',
                    'locked' => !isloggedin() || !$cm->uservisible,
                ];
            }

            $sections[] = [
                'name' => get_section_name($course, $section),
                'activities' => $activities,
                'hasactivities' => !empty($activities),
                'activitycount' => count($activities),
            ];
        }

        return array_merge($card, [
            'summary' => format_text($course->summary, FORMAT_HTML, ['context' => $context]),
            'video' => $videourl,
            'hasvideo' => !empty($videourl),
            'isvideofile' => $isvideofile,
            'hasteacher' => !empty($teacher),
            'teachername' => $teacher['name'] ?? '',
            'teacherprofileurl' => $teacher['profileurl'] ?? '',
            'teacherimage' => $teacher['image'] ?? '',
            'sections' => $sections,
            'hassections' => !empty($sections),
        ]);
    }

    /**
     * Return the uploaded banner video for a course, if one exists.
     *
     * @param int $courseid
     * @return \stored_file|null
     */
    private static function get_uploaded_banner_video(int $courseid): ?\stored_file {
        $files = get_file_storage()->get_area_files(
            context_course::instance($courseid)->id,
            'format_edukav',
            'bannervideo',
            0,
            'filename,filepath',
            false
        );
        return $files ? reset($files) : null;
    }
}
