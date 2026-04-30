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
    if ($context->contextlevel !== CONTEXT_SYSTEM) {
        return false;
    }

    if ($filearea !== 'partner_logo') {
        return false;
    }

    require_login();

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
