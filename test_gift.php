<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\GiftParser\GiftParser;
use App\Models\Course;

$giftPath = storage_path('app/private/КЛЕН/GIFT/01 Конструкция самолета и его летная эксплуатация.txt');

$course = Course::first();
echo 'Course ID: '.$course->id.', Aircraft ID: '.$course->aircraft_id."\n";

$parser = new GiftParser;
$parser->setAircraftId($course->aircraft_id);

$parser = new GiftParser;
$parser->setAircraftId($course->aircraft_id);

// Check what's in the file
$giftString = file_get_contents($giftPath);
$lines = explode("\n", $giftString);

$questionLines = 0;
$categoryLines = 0;
$currentCategoryId = null;
$currentAukstructureId = null;

foreach ($lines as $i => $line) {
    $line = trim($line);
    if (empty($line)) {
        continue;
    }

    if (str_starts_with($line, '$CATEGORY:')) {
        $categoryLines++;
        if ($categoryLines <= 3) {
            echo "CATEGORY line $i: ".substr($line, 0, 60)."\n";
        }

        // Inside parse, it would call processCategory
        // Let's see what processCategory does
        $categoryContent = substr($line, strlen('$CATEGORY:'));

        // This is what processCategory does:
        $categoryLine = preg_replace('/^\$system\$\//', '', $categoryContent);
        $parts = array_filter(explode('/', $categoryLine), fn ($p) => ! empty(trim($p)));
        $parts = array_values($parts);

        if (count($parts) >= 2) {
            $specialty = trim(preg_replace('/^\d+\s*/', '', $parts[count($parts) - 2] ?? ''));
            $razdel = trim(preg_replace('/^\d+/', '', $parts[count($parts) - 1] ?? ''));
            if ($categoryLines <= 3) {
                echo "  Specialty: $specialty, Razdel: $razdel\n";
            }

            $category = \App\Models\Category::where('title', 'like', "%{$specialty}%")
                ->where('aircraft_id', $course->aircraft_id)
                ->first();

            if ($category) {
                $currentCategoryId = $category->id;
                if ($categoryLines <= 3) {
                    echo "  Found category ID: $currentCategoryId\n";
                }

                $aukstructure = \App\Models\Aukstructure::where('title', 'like', "%{$razdel}%")
                    ->where('course_id', $course->id)
                    ->first();

                if ($aukstructure) {
                    $currentAukstructureId = $aukstructure->id;
                    if ($categoryLines <= 3) {
                        echo "  Found aukstructure ID: $currentAukstructureId\n";
                    }
                }
            }
        }
    } elseif ($currentAukstructureId) {
        $isQuestion = str_starts_with($line, '<p>') && str_ends_with($line, '{');
        if ($isQuestion) {
            $questionLines++;
            if ($questionLines <= 3) {
                echo "QUESTION line $i: ".substr($line, 0, 50)."...\n";
            }
        }
    }
}

echo "\nTotal: $categoryLines categories, $questionLines questions\n";
