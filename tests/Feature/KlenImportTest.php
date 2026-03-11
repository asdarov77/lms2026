<?php

namespace Tests\Feature;

use App\Services\KlenImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlenImportTest extends TestCase
{
    use RefreshDatabase;

    private KlenImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importService = new KlenImportService();
    }

    public function test_klen_directory_exists(): void
    {
        $basePath = storage_path('app/private/КЛЕН');
        $this->assertDirectoryExists($basePath);
    }

    public function test_import_service_can_list_courses(): void
    {
        $basePath = storage_path('app/private/КЛЕН');
        $directories = scandir($basePath);
        $directories = array_diff($directories, ['.', '..']);

        $this->assertGreaterThan(0, count($directories));
    }

    public function test_import_course_01(): void
    {
        $stats = $this->importService->import('01');

        $this->assertEquals(0, count($stats['errors']));
        $this->assertGreaterThan(0, $stats['courses']);
        $this->assertGreaterThan(0, $stats['topics']);
        $this->assertGreaterThan(0, $stats['materials']);
    }

    public function test_import_all_courses(): void
    {
        $stats = $this->importService->import();

        $this->assertEquals(0, count($stats['errors']));
        $this->assertGreaterThan(0, $stats['courses']);
    }
}
