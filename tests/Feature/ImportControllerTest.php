<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_aircrafts_returns_list()
    {
        $user = User::factory()->create([
            'fio' => 'Admin',
            'role' => 'Администратор',
        ]);

        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/import/aircrafts');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_import_run_creates_courses()
    {
        $user = User::factory()->create([
            'fio' => 'Admin',
            'role' => 'Администратор',
        ]);

        $token = $user->createToken('t')->plainTextToken;

        // Проверяем что есть КЛЕН в хранилище
        if (! is_dir(storage_path('app/private/КЛЕН'))) {
            $this->markTestSkipped('Директория КЛЕН не найдена');
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/import/run', [
                'aircraft_path' => 'КЛЕН',
                'clear_db' => true,
            ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertGreaterThan(0, $data['result']['courses_created']);

        // Проверяем что данные созданы в БД
        $this->assertDatabaseHas('aircrafts', [
            'path' => 'КЛЕН',
        ]);
    }
}
