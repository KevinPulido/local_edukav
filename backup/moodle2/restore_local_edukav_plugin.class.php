<?php
defined('MOODLE_INTERNAL') || die();

/** Restore course library resources and their files into the target course. */
class restore_local_edukav_plugin extends restore_local_plugin {
    public function define_course_plugin_structure(): array {
        return [
            new restore_path_element(
                'local_edukav_library_resource',
                $this->get_pathfor('/resources/resource')
            ),
        ];
    }

    public function process_local_edukav_library_resource($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = (int)$data->id;
        unset($data->id);
        $data->courseid = $this->task->get_courseid();
        $data->createdby = $this->get_mappingid('user', (int)$data->createdby, 0);
        $data->modifiedby = $this->get_mappingid('user', (int)$data->modifiedby, 0);
        $newid = $DB->insert_record('edukav_course_library', $data);
        $this->set_mapping('local_edukav_library_resource', $oldid, $newid, true);
    }

    public function after_execute_course(): void {
        $this->add_related_files(
            'local_edukav',
            'course_library_resource',
            'local_edukav_library_resource'
        );
        $this->add_related_files(
            'local_edukav',
            'course_library_cover',
            'local_edukav_library_resource'
        );
    }
}
