<?php
defined('MOODLE_INTERNAL') || die();

/** Include course library resources in Moodle course backups. */
class backup_local_edukav_plugin extends backup_local_plugin {
    public function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element();
        $wrapper = new backup_nested_element($this->get_recommended_name());
        $resources = new backup_nested_element('resources');
        $resource = new backup_nested_element('resource', ['id'], [
            'title',
            'description',
            'category',
            'resourcetype',
            'level',
            'externalurl',
            'featured',
            'visible',
            'sortorder',
            'createdby',
            'modifiedby',
            'timecreated',
            'timemodified',
        ]);

        $plugin->add_child($wrapper);
        $wrapper->add_child($resources);
        $resources->add_child($resource);
        $resource->set_source_table('edukav_course_library', ['courseid' => backup::VAR_COURSEID]);
        $resource->annotate_ids('user', 'createdby');
        $resource->annotate_ids('user', 'modifiedby');
        $resource->annotate_files('local_edukav', 'course_library_resource', 'id');
        $resource->annotate_files('local_edukav', 'course_library_cover', 'id');

        return $plugin;
    }
}
