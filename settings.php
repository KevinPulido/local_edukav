<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add(
        'localplugins',
        new admin_externalpage(
            'local_edukav_partners',
            'Edukav partners',
            new moodle_url('/local/edukav/partners.php'),
            'moodle/site:config'
        )
    );
}
