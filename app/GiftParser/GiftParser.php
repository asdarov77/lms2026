<?php

namespace App\GiftParser;

use App\Models\Answer;
use App\Models\Aukstructure;
use App\Models\Category;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Support\Facades\Log;

class GiftParser
{
    protected array $results = [
        'questions_created' => 0,
        'answers_created' => 0,
        'errors' => [],
    ];

    protected ?int $aircraftId = null;

    public function setAircraftId(int $aircraftId): void
    {
        $this->aircraftId = $aircraftId;
    }

    public function parse(string $giftFilePath): array
    {
        try {
            Log::info('parse called for: '.$giftFilePath.' aircraftId='.$this->aircraftId);

            if (! file_exists($giftFilePath)) {
                Log::error("GIFT файл не найден: {$giftFilePath}");
                $this->results['errors'][] = "Файл не найден: {$giftFilePath}";

                return $this->results;
            }

            $giftString = file_get_contents($giftFilePath);
            if (empty($giftString)) {
                Log::warning("GIFT файл пуст: {$giftFilePath}");
                $this->results['errors'][] = "файл пуст: {$giftFilePath}";

                return $this->results;
            }

            Log::info("Начало парсинга GIFT файла: {$giftFilePath}");

            $lines = explode("\n", $giftString);
            $currentCategoryId = null;
            $currentAukstructureId = null;

            foreach ($lines as $index => $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                if (str_starts_with($line, '$CATEGORY:')) {
                    $categoryContent = substr($line, strlen('$CATEGORY:'));
                    $this->processCategory($categoryContent, $currentCategoryId, $currentAukstructureId);
                } elseif ($this->isQuestionLine($line)) {
                    $currentQuestionId = $this->createQuestion($line, $currentCategoryId, $currentAukstructureId);
                    if ($currentQuestionId && isset($lines[$index + 1])) {
                        $nextLines = [];
                        for ($i = $index + 1; $i < min($index + 10, count($lines)); $i++) {
                            $nextLine = trim($lines[$i]);
                            if (empty($nextLine) || $nextLine === '}') {
                                break;
                            }
                            if (strpos($nextLine, '{') !== false) {
                                break;
                            }
                            $nextLines[] = $nextLine;
                        }
                        foreach ($nextLines as $answerLine) {
                            $this->createAnswer($answerLine, $currentQuestionId);
                        }
                    }
                }
            }

            Log::info("Завершение парсинга GIFT файла: {$giftFilePath}", $this->results);

            return $this->results;
        } catch (\Exception $e) {
            Log::error('Ошибка парсинга GIFT файла: '.$e->getMessage());
            $this->results['errors'][] = 'Ошибка парсинга: '.$e->getMessage();

            return $this->results;
        }
    }

    private function processCategory(string $categoryLine, ?int &$categoryId, ?int &$aukstructureId): void
    {
        $categoryLine = preg_replace('/^\$system\$\//', '', $categoryLine);

        $parts = array_filter(explode('/', $categoryLine), fn ($p) => ! empty(trim($p)));
        $parts = array_values($parts);

        if (count($parts) < 2) {
            Log::warning("Неверный формат категории: {$categoryLine}");

            return;
        }

        $specialty = trim(preg_replace('/^\d+\s*/', '', $parts[count($parts) - 2] ?? ''));
        $razdel = trim(preg_replace('/^\d+/', '', $parts[count($parts) - 1] ?? ''));

        if (empty($specialty) || empty($razdel)) {
            Log::warning("Не удалось извлечь специальность или раздел из: {$categoryLine}");

            return;
        }

        $course = Course::where('aircraft_id', $this->aircraftId)->first();
        if (! $course) {
            Log::warning("Курс не найден для aircraft_id: {$this->aircraftId}");
            $categoryId = null;
            $aukstructureId = null;

            return;
        }

        Log::info("processCategory: specialty=$specialty, razdel=$razdel, course_id={$course->id}");

        $category = Category::where('title', 'like', "%{$specialty}%")
            ->where('aircraft_id', $this->aircraftId)
            ->first();

        if (! $category) {
            $category = Category::create([
                'title' => $specialty,
                'code' => substr($specialty, 0, 50),
                'description' => 'Создано из GIFT',
                'aircraft_id' => $this->aircraftId,
            ]);
            Log::info("Создана категория: {$specialty}");
        }
        $categoryId = $category->id;

        $aukstructure = Aukstructure::where('title', 'like', "%{$razdel}%")
            ->where('course_id', $course->id)
            ->first();

        if (! $aukstructure) {
            $aukstructure = Aukstructure::create([
                'title' => $razdel,
                'course_id' => $course->id,
                'parent_id' => 0,
                'type' => 3,
                'description' => 'модуль',
            ]);
            Log::info("Создана aukstructure: {$razdel}");
        }
        $aukstructureId = $aukstructure->id;
    }

    private function isQuestionLine(string $line): bool
    {
        return str_starts_with($line, '::') && (str_ends_with($line, '{') || str_ends_with($line, '{ '));
    }

    private function createQuestion(string $line, ?int $categoryId, ?int $aukstructureId): ?int
    {
        $questionText = $this->extractQuestionText($line);
        if (empty($questionText)) {
            return null;
        }

        try {
            $question = Question::create([
                'question_text' => $questionText,
                'category_id' => $categoryId,
                'aukstructure_id' => $aukstructureId,
            ]);
            $this->results['questions_created']++;

            return $question->id;
        } catch (\Exception $e) {
            Log::error('Ошибка создания вопроса: '.$e->getMessage());
            $this->results['errors'][] = 'Ошибка создания вопроса: '.$e->getMessage();

            return null;
        }
    }

    private function extractQuestionText(string $line): string
    {
        $line = trim($line);

        $questionText = preg_replace('/^::[^:]+::/', '', $line);
        $questionText = preg_replace('/\{\s*$/', '', $questionText);

        return trim($questionText);
    }

    private function createAnswer(string $line, int $questionId): void
    {
        $line = trim($line);
        if (empty($line)) {
            return;
        }

        $isCorrect = str_starts_with($line, '=');

        if ($isCorrect) {
            $answerText = substr($line, 1);
        } elseif (str_starts_with($line, '~')) {
            $answerText = substr($line, 1);
        } else {
            return;
        }

        $answerText = trim($answerText);
        if (empty($answerText)) {
            return;
        }

        try {
            Answer::create([
                'answer' => $answerText,
                'question_id' => $questionId,
                'is_correct' => $isCorrect,
            ]);
            $this->results['answers_created']++;
        } catch (\Exception $e) {
            Log::error('Ошибка создания ответа: '.$e->getMessage());
            $this->results['errors'][] = 'Ошибка создания ответа: '.$e->getMessage();
        }
    }
}
