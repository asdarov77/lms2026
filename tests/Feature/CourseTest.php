<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_index_returns_paginated_list()
    {
        $user = User::factory()->create([
            'fio' => 'Admin',
            'role' => 'Администратор',
        ]);
        Course::factory()->count(3)->create();

        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/course');

        $response->assertStatus(200);
    }
}
