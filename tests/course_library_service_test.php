<?php
namespace local_edukav;

defined('MOODLE_INTERNAL') || die();

use local_edukav\service\course_library_service;

/** Tests for the per-course library service. */
final class course_library_service_test extends \advanced_testcase {
    public function test_resource_is_scoped_to_its_course_and_exported(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $resourceid = course_library_service::save_resource((int)$course->id, (object)[
            'title' => 'Guía de estudio',
            'description' => 'Material de apoyo para el curso.',
            'category' => 'Estudio',
            'resourcetype' => 'pdf',
            'level' => 'basic',
            'externalurl' => 'https://example.com/guide.pdf',
            'featured' => 1,
            'visible' => 1,
            'sortorder' => 10,
        ]);

        $library = course_library_service::get_library_context((int)$course->id);
        $this->assertTrue($library['hasresources']);
        $this->assertTrue($library['hasfeatured']);
        $this->assertCount(1, $library['resources']);
        $this->assertSame($resourceid, $library['resources'][0]['id']);
        $this->assertSame('pdf', $library['resources'][0]['type']);
        $this->assertSame('estudio', $library['resources'][0]['categorykey']);

        course_library_service::delete_resource($resourceid, (int)$course->id);
        $this->assertFalse(course_library_service::get_library_context((int)$course->id)['hasresources']);
    }

    public function test_resource_requires_a_url_or_file(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $this->expectException(\invalid_parameter_exception::class);
        course_library_service::save_resource((int)$course->id, (object)[
            'title' => 'Sin contenido',
            'resourcetype' => 'document',
            'level' => 'basic',
            'externalurl' => '',
            'visible' => 1,
        ]);
    }
}
