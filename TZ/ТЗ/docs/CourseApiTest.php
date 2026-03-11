<?php
use Tests\TestCase;

class CourseApiTest extends TestCase
{
    public function test_courses_index_requires_auth()
    {
        $response = $this->getJson('/api/v1/courses');
        $response->assertStatus(200);
    }
}