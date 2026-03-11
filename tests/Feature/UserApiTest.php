<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $group = \App\Models\Group::create([
            'groupname' => 'Test Group',
            'groupdescription' => 'Test Description',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/register', [
            'fio' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'Пользователь',
            'group_id' => $group->id,
        ]);

        $response->assertStatus(201);
    }

    public function test_csrf_cookie_endpoint(): void
    {
        $response = $this->get('/api/sanctum/csrf-cookie');

        $response->assertStatus(200);
    }
}
