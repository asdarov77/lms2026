<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Topic;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use DOMDocument;

class KlenImportService
{
    private string $basePath;
    private array $stats = [
        'courses' => 0,
        'topics' => 0,
        'materials' => 0,
        'errors' => []
    ];

    public function __construct()
    {
        $this->basePath = storage_path('app/private/КЛЕН');
    }

    public function import(string $courseCode = null): array
    {
        $this->stats = [
            'courses' => 0,
            'topics' => 0,
            'materials' => 0,
            'errors' => []
        ];

        if (!is_dir($this->basePath)) {
            $this->stats['errors'][] = "Directory not found: {$this->basePath}";
            return $this->stats;
        }

        $directories = scandir($this->basePath);
        $directories = array_diff($directories, ['.', '..']);

        foreach ($directories as $dir) {
            if ($courseCode && $dir !== $courseCode) {
                continue;
            }

            if (is_dir("{$this->basePath}/{$dir}")) {
                $this->importCourse($dir);
            }
        }

        return $this->stats;
    }

    private function importCourse(string $courseCode): void
    {
        $coursePath = "{$this->basePath}/{$courseCode}";

        // Создаем курс
        $course = Course::firstOrCreate(
            ['path' => $courseCode],
            [
                'title' => "Курс {$courseCode} (КЛЕН)",
                'short_description' => "Импортированный курс из КЛЕН",
                'long_description' => "Курс импортирован из директории storage/app/private/КЛЕН/{$courseCode}",
                'visible' => true,
            ]
        );

        $this->stats['courses']++;

        // Находим HTML файлы (уроки)
        $htmlFiles = glob("{$coursePath}/*.html");

        foreach ($htmlFiles as $htmlFile) {
            $this->importLesson($course, $htmlFile, $courseCode);
        }
    }

    private function importLesson(Course $course, string $htmlFile, string $courseCode): void
    {
        $filename = basename($htmlFile);
        $title = pathinfo($filename, PATHINFO_FILENAME);

        if (empty($title)) {
            $this->stats['errors'][] = "Empty title for file: {$filename}";
            return;
        }

        // Парсим HTML файл для получения контента
        $content = file_get_contents($htmlFile);
        $parsedContent = $this->parseHtmlContent($content, $courseCode);

        // Создаем тему (topic)
        $topic = Topic::where('course_id', $course->id)
            ->where('title', $title)
            ->first();

        if (!$topic) {
            $topic = Topic::create([
                'course_id' => $course->id,
                'title' => $title,
                'description' => "Урок: {$title}",
                'sort_order' => $this->getOrderFromFilename($filename),
                'status' => 'active'
            ]);
        }

        $this->stats['topics']++;

        // Создаем материал для HTML контента
        Material::create([
            'title' => $title,
            'content' => $parsedContent,
            'topic_id' => $topic->id,
            'order' => 1,
            'type' => 'html',
            'file_path' => "storage/app/private/КЛЕН/{$courseCode}/{$filename}",
        ]);

        $this->stats['materials']++;

        // Находим связанные медиа-файлы (SVG, MP4)
        $this->importMediaFiles($topic, $courseCode, $htmlFile);
    }

    private function importMediaFiles(Topic $topic, string $courseCode, string $htmlFile): void
    {
        $coursePath = "{$this->basePath}/{$courseCode}";
        $htmlDir = dirname($htmlFile);

        // Получаем все файлы из директории курса
        $files = scandir($coursePath);
        $files = array_diff($files, ['.', '..']);

        // Парсим HTML для поиска ссылок на файлы
        $htmlContent = file_get_contents($htmlFile);
        $pattern = '/(?:src|href)=["\']([^"\']+)["\']/i';
        preg_match_all($pattern, $htmlContent, $matches);

        $referencedFiles = $matches[1] ?? [];

        foreach ($files as $file) {
            $filePath = "{$coursePath}/{$file}";

            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                // Импортируем только если файл упоминается в HTML
                if (in_array($file, $referencedFiles)) {
                    $this->createMaterialFromFile($topic, $file, $courseCode, $extension);
                }
            }
        }
    }

    private function createMaterialFromFile(Topic $topic, string $filename, string $courseCode, string $extension): void
    {
        $type = match($extension) {
            'svg' => 'image',
            'mp4' => 'video',
            'gif' => 'image',
            default => 'file'
        };

        Material::create([
            'title' => $filename,
            'content' => null,
            'topic_id' => $topic->id,
            'order' => $this->stats['materials'] + 1,
            'type' => $type,
            'file_path' => "storage/app/private/КЛЕН/{$courseCode}/{$filename}",
        ]);

        $this->stats['materials']++;
    }

    private function parseHtmlContent(string $html, string $courseCode): string
    {
        // Обновляем пути к файлам в HTML
        $html = preg_replace(
            '/(src|href)=["\']([^"\']+)["\']/i',
            '$1="/storage/КЛЕН/' . $courseCode . '/$2"',
            $html
        );

        return $html;
    }

    private function getOrderFromFilename(string $filename): int
    {
        // Извлекаем порядок из имени файла (например, "1.1.1" -> 111)
        preg_match('/^(\d+)\.(\d+)\.(\d+)/', $filename, $matches);

        if (count($matches) >= 4) {
            return (int)($matches[1] . $matches[2] . $matches[3]);
        }

        return 0;
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}
