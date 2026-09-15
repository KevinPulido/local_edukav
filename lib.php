<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Serves files for local_edukav.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function local_edukav_pluginfile(
    $course,
    $cm,
    $context,
    string $filearea,
    array $args,
    bool $forcedownload,
    array $options = []
): bool {
    $libraryareas = ['course_library_resource', 'course_library_cover'];
    if ($context->contextlevel === CONTEXT_COURSE && in_array($filearea, $libraryareas, true)) {
        $itemid = (int)array_shift($args);
        $resource = \local_edukav\repository\course_library_repository::get_by_id($itemid);
        if (!$resource || (int)$resource->courseid !== (int)$context->instanceid) {
            return false;
        }
        $course = get_course((int)$resource->courseid);
        require_login($course);
        if (empty($resource->visible) &&
                !has_capability('local/edukav:managecourselibrary', $context)) {
            return false;
        }
        $filename = array_pop($args);
        $filepath = '/' . implode('/', $args) . '/';
        if ($filepath === '//') {
            $filepath = '/';
        }
        $file = get_file_storage()->get_file(
            $context->id,
            'local_edukav',
            $filearea,
            $itemid,
            $filepath,
            $filename
        );
        if (!$file || $file->is_directory()) {
            return false;
        }
        send_stored_file($file, 86400, 0, $filearea === 'course_library_resource', $options);
        return true;
    }

    if ($context->contextlevel !== CONTEXT_SYSTEM || $filearea !== 'partner_logo') {
        return false;
    }

    $itemid = (int)array_shift($args);
    $filename = array_pop($args);
    $filepath = '/' . implode('/', $args) . '/';
    if ($filepath === '//') {
        $filepath = '/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_edukav', $filearea, $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
    return true;
}

/**
 * Persist a partner logo uploaded through a filemanager draft area.
 *
 * @param int $partnerid
 * @param int $draftitemid
 * @return void
 */
function local_edukav_save_partner_logo(int $partnerid, int $draftitemid): void {
    if ($partnerid <= 0 || $draftitemid <= 0) {
        return;
    }

    $context = context_system::instance();
    file_save_draft_area_files(
        $draftitemid,
        $context->id,
        'local_edukav',
        'partner_logo',
        $partnerid,
        [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]
    );
}


/**
 * Extract the YouTube video id from common URL formats.
 *
 * @param string|null $url
 * @return string|null
 */
function local_edukav_extract_video_id(?string $url): ?string{
    $url = trim((string)$url);
    if ($url === '') {
        return null;
    }

    $parts = parse_url($url);
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $queryparams);
        if (!empty($queryparams['v'])) {
            return $queryparams['v'];
        }
    }

    if (preg_match('~youtube\.com/embed/([^?&/]+)~i', $url, $matches)) {
        return $matches[1];
    }

    if (preg_match('~youtube\.com/watch\?v=([^?&/]+)~i', $url, $matches)) {
        return $matches[1];
    }

    if (preg_match('~youtu\.be/([^?&/]+)~i', $url, $matches)) {
        return $matches[1];
    }

    return null;
}

/** Add the course library to the course navigation. */
function local_edukav_extend_navigation_course($navigation, $course, $context): void {
    $navigation->add(
        get_string('course_library', 'local_edukav'),
        new moodle_url('/local/edukav/library.php', ['courseid' => $course->id]),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_edukav_course_library'
    );
}

/**
 * Return the participants page available to the current user.
 *
 * @param stdClass $course
 * @param context_course $context
 * @return moodle_url
 */
function local_edukav_get_course_participants_url(stdClass $course, context_course $context): moodle_url {
    if (has_capability('moodle/course:enrolreview', $context)) {
        return new moodle_url('/user/index.php', ['id' => $course->id]);
    }

    return new moodle_url('/local/edukav/participants.php', ['id' => $course->id]);
}
