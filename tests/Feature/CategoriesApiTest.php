<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index_returns_list()
    {
        $user = User::factory()->create([
            'fio' => 'Admin2',
            'role' => 'Администратор',
        ]);

        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/categories');

        $response->assertStatus(200);
    }
}
