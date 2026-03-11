<?php

namespace App\Http\Controllers;

use App\Services\KlenImportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KlenImportController extends Controller
{
    private KlenImportService $importService;

    public function __construct(KlenImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index(): JsonResponse
    {
        $basePath = storage_path('app/private/КЛЕН');

        if (!is_dir($basePath)) {
            return response()->json(['error' => 'КЛЕН directory not found'], 404);
        }

        $directories = scandir($basePath);
        $directories = array_diff($directories, ['.', '..']);

        $courses = [];

        foreach ($directories as $dir) {
            $coursePath = "{$basePath}/{$dir}";
            if (is_dir($coursePath)) {
                $htmlFiles = glob("{$coursePath}/*.html");
                $courses[] = [
                    'code' => $dir,
                    'lessons_count' => count($htmlFiles),
                    'path' => $coursePath
                ];
            }
        }

        return response()->json(['courses' => $courses]);
    }

    public function import(Request $request): JsonResponse
    {
        $courseCode = $request->input('course_code');

        try {
            $stats = $this->importService->import($courseCode);

            if (!empty($stats['errors'])) {
                return response()->json([
                    'success' => false,
                    'stats' => $stats,
                    'errors' => $stats['errors']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function importAll(): JsonResponse
    {
        try {
            $stats = $this->importService->import();

            if (!empty($stats['errors'])) {
                return response()->json([
                    'success' => false,
                    'stats' => $stats,
                    'errors' => $stats['errors']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'All courses imported successfully',
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
