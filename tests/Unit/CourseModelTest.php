<?php

namespace Tests\Unit;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_can_be_created(): void
    {
        $course = Course::create([
            'title' => 'Test Course',
            'short_description' => 'Test Description',
            'visible' => true,
        ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'Test Course',
        ]);
    }

    public function test_course_visible_attribute(): void
    {
        $course = Course::create([
            'title' => 'Test Course',
            'visible' => true,
        ]);

        $this->assertTrue($course->visible);
    }

    public function test_course_can_have_group2learnings(): void
    {
        $course = Course::create([
            'title' => 'Test Course',
            'short_description' => 'Test Description',
            'visible' => true,
        ]);

        $this->assertTrue(method_exists($course, 'group2learnings'));
    }

    public function test_course_can_have_aukstructures(): void
    {
        $course = Course::create([
            'title' => 'Test Course',
            'visible' => true,
        ]);

        $this->assertTrue(method_exists($course, 'aukstructures'));
    }

    public function test_course_uses_filterable_trait(): void
    {
        $course = new Course;
        
        $this->assertTrue(in_array('App\Traits\Filterable', class_uses($course)));
    }
}
