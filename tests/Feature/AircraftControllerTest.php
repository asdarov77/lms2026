<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AircraftControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_classes_index_returns_list()
    {
        $user = User::factory()->create([
            'fio' => 'Admin',
            'role' => 'Администратор',
        ]);

        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/classes');

        $response->assertStatus(200);
    }

    public function test_classesfs_returns_filesystem_classes()
    {
        $user = User::factory()->create([
            'fio' => 'Admin',
            'role' => 'Администратор',
        ]);

        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/classesfs');

        $response->assertStatus(200);
    }

    public function test_storeclasses_creates_aircraft()
    {
        $user = User::factory()->create([
            'fio' => 'Admin',
            'role' => 'Администратор',
        ]);

        $token = $user->createToken('t')->plainTextToken;

        // Создаем тестовую директорию
        $testPath = storage_path('app/public/private/TestAircraft');
        if (! is_dir($testPath)) {
            mkdir($testPath, 0755, true);
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/classes', [
                'title' => 'Test Aircraft',
                'path' => 'TestAircraft',
            ]);

        $response->assertStatus(201);

        // Проверяем что запись создана в БД
        $this->assertDatabaseHas('aircrafts', [
            'title' => 'Test Aircraft',
            'path' => 'TestAircraft',
        ]);
    }
}
