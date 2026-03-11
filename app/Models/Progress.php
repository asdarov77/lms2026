<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Progress extends Model
{
    use HasFactory;

    /**
     * Таблица, связанная с моделью.
     *
     * @var string
     */
    protected $table = 'progress';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'completed_topics',
        'completed_topics_count',
        'last_topic_id',
        'last_position',
        'score',
        'time_spent',
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам.
     *
     * @var array
     */
    protected $casts = [
        'completed_topics' => 'array',
        'last_position' => 'array',
        'completed_topics_count' => 'integer',
        'score' => 'float',
        'time_spent' => 'integer',
    ];

    /**
     * Правила статусов прогресса
     */
    const STATUSES = [
        'not_started' => 'Не начат',
        'in_progress' => 'В процессе',
        'completed' => 'Завершен',
    ];

    /**
     * Отношение к пользователю
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Отношение к курсу
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Отношение к последней просмотренной теме
     */
    public function lastTopic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'last_topic_id');
    }

    /**
     * Получить завершенные темы как массив.
     */
    public function getCompletedTopicsAttribute($value): array
    {
        return json_decode($value ?: '[]', true);
    }

    /**
     * Установить завершенные темы из массива.
     *
     * @param  array  $value
     */
    public function setCompletedTopicsAttribute($value): void
    {
        $this->attributes['completed_topics'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Получить последнюю позицию как массив.
     */
    public function getLastPositionAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    /**
     * Установить последнюю позицию из массива.
     *
     * @param  array  $value
     */
    public function setLastPositionAttribute($value): void
    {
        $this->attributes['last_position'] = $value ? (is_array($value) ? json_encode($value) : $value) : null;
    }

    /**
     * Получить процент выполнения курса
     */
    public function getCompletionPercentageAttribute()
    {
        if (! $this->course) {
            return 0;
        }

        $totalTopics = $this->course->topics()->count();

        if ($totalTopics === 0) {
            return 0;
        }

        return round(($this->completed_topics_count / $totalTopics) * 100);
    }

    /**
     * Отметить тему как завершенную
     */
    public function markTopicCompleted($topicId)
    {
        $completedTopics = $this->completed_topics ?? [];

        // Если тема уже отмечена как завершенная, ничего не делаем
        if (in_array($topicId, $completedTopics)) {
            return;
        }

        // Добавляем тему в список завершенных
        $completedTopics[] = $topicId;
        $this->completed_topics = $completedTopics;
        $this->completed_topics_count = count($completedTopics);

        // Обновляем статус, если все темы завершены
        $totalTopics = $this->course->topics()->count();
        if ($this->completed_topics_count >= $totalTopics) {
            $this->status = 'completed';

            // Записываем в активность завершение курса
            Activity::log('course_completed', [
                'course_id' => $this->course_id,
                'course_name' => $this->course->title,
            ]);
        } else {
            $this->status = 'in_progress';
        }

        $this->save();
    }

    /**
     * Проверить, является ли тема завершенной.
     */
    public function isTopicCompleted(int $topicId): bool
    {
        return in_array($topicId, $this->completed_topics);
    }

    /**
     * Отметить тему как завершенную
     *
     * @param  int  $topicId
     * @return void
     */
    public function markTopicAsCompleted($topicId)
    {
        $completedTopics = $this->completed_topics ?? [];

        // Проверяем, есть ли уже эта тема в списке завершенных
        if (! in_array($topicId, $completedTopics)) {
            $completedTopics[] = $topicId;
            $this->completed_topics = $completedTopics;
            $this->completed_topics_count = count($completedTopics);

            // Обновляем статус прогресса
            if ($this->status === 'not_started') {
                $this->status = 'in_progress';
            }
        }
    }

    /**
     * Отметить тему как незавершенную
     *
     * @param  int  $topicId
     * @return void
     */
    public function markTopicAsIncomplete($topicId)
    {
        $completedTopics = $this->completed_topics ?? [];

        // Проверяем, есть ли эта тема в списке завершенных
        if (in_array($topicId, $completedTopics)) {
            $completedTopics = array_diff($completedTopics, [$topicId]);
            $this->completed_topics = array_values($completedTopics);
            $this->completed_topics_count = count($completedTopics);

            // Обновляем статус прогресса
            if (empty($completedTopics)) {
                $this->status = 'not_started';
            }
        }
    }

    /**
     * Получить процент завершения курса
     *
     * @return int
     */
    public function getCompletionPercentage()
    {
        $totalTopics = Topic::where('course_id', $this->course_id)->count();

        if ($totalTopics > 0) {
            return round(($this->completed_topics_count / $totalTopics) * 100);
        }

        return 0;
    }

    /**
     * Сбросить прогресс
     *
     * @return void
     */
    public function reset()
    {
        $this->status = 'not_started';
        $this->completed_topics = [];
        $this->completed_topics_count = 0;
        $this->last_topic_id = null;
        $this->last_position = null;
        $this->score = 0;
        // Не сбрасываем time_spent, чтобы сохранить статистику
        $this->save();
    }

    /**
     * Создать или обновить запись прогресса для текущего пользователя и курса
     *
     * @param  int  $courseId
     * @return Progress
     */
    public static function createOrUpdateForUser($courseId, array $attributes = [])
    {
        $userId = Auth::id();

        $progress = self::firstOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['status' => 'not_started']
        );

        if (! empty($attributes)) {
            $progress->fill($attributes);
            $progress->save();
        }

        return $progress;
    }
}
