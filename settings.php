<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add(
        'localplugins',
        new admin_category('local_edukav_settings', get_string('pluginname', 'local_edukav'))
    );
    $ADMIN->add(
        'local_edukav_settings',
        new admin_externalpage(
            'local_edukav_faqs',
            get_string('managefaqs', 'local_edukav'),
            new moodle_url('/local/edukav/faq/index.php'),
            'moodle/site:config'
        )
    );
    $ADMIN->add(
        'local_edukav_settings',
        new admin_externalpage(
            'local_edukav_faq_categories',
            get_string('managefaqcategories', 'local_edukav'),
            new moodle_url('/local/edukav/faq/categories.php'),
            'moodle/site:config'
        )
    );
    $ADMIN->add(
        'local_edukav_settings',
        new admin_externalpage(
            'local_edukav_tutorials',
            get_string('managetutorials', 'local_edukav'),
            new moodle_url('/local/edukav/tutorial/index.php'),
            'moodle/site:config'
        )
    );
    $ADMIN->add(
        'local_edukav_settings',
        new admin_externalpage(
            'local_edukav_partners',
            get_string('managepartners', 'local_edukav'),
            new moodle_url('/local/edukav/partners.php'),
            'moodle/site:config'
        )
    );
}
