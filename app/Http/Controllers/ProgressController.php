<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use App\Models\Course;
use App\Models\Topic;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProgressController extends Controller
{
    /**
     * Получить прогресс текущего пользователя по указанному курсу
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function getUserProgress($courseId)
    {
        $progress = Progress::where('user_id', Auth::id())
                    ->where('course_id', $courseId)
                    ->with('course')
                    ->first();

        if (!$progress) {
            $course = Course::findOrFail($courseId);
            
            // Создаем новую запись прогресса
            $progress = Progress::create([
                'user_id' => Auth::id(),
                'course_id' => $courseId,
                'status' => 'not_started',
                'completed_topics' => json_encode([]),
                'completed_topics_count' => 0
            ]);
            
            $progress->load('course');
        }

        return response()->json($progress);
    }

    /**
     * Получить список прогресса пользователя по всем курсам
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllUserProgress()
    {
        $progress = Progress::where('user_id', Auth::id())
                    ->with('course')
                    ->get();

        return response()->json($progress);
    }

    /**
     * Обновить прогресс для указанной темы
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $courseId
     * @param  int  $topicId
     * @return \Illuminate\Http\Response
     */
    public function updateTopicProgress(Request $request, $courseId, $topicId)
    {
        $request->validate([
            'completed' => 'required|boolean',
            'position' => 'sometimes|array',
            'time_spent' => 'sometimes|integer',
            'score' => 'sometimes|numeric|min:0|max:100',
        ]);

        $userId = Auth::id();
        $progress = Progress::where('user_id', $userId)
                    ->where('course_id', $courseId)
                    ->first();
        
        if (!$progress) {
            $course = Course::findOrFail($courseId);
            
            $progress = Progress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'not_started',
                'completed_topics' => json_encode([]),
                'completed_topics_count' => 0
            ]);
        }

        // Проверяем существование темы в курсе
        $topic = Topic::where('id', $topicId)
                ->where('course_id', $courseId)
                ->firstOrFail();

        // Обновляем последнюю посещенную тему
        $progress->last_topic_id = $topicId;
        
        // Обновляем позицию, если предоставлена
        if ($request->has('position')) {
            $progress->last_position = json_encode($request->position);
        }
        
        // Обновляем затраченное время, если предоставлено
        if ($request->has('time_spent')) {
            $progress->time_spent += $request->time_spent;
        }
        
        // Обновляем оценку, если предоставлена
        if ($request->has('score')) {
            $progress->score = $request->score;
        }

        // Если тема помечена как завершенная
        if ($request->completed) {
            $progress->markTopicCompleted($topicId);
            
            ActivityLogger::log('completed topic', 'Пользователь завершил тему: ' . $topic->title);
        }

        $progress->save();

        return response()->json($progress);
    }

    /**
     * Получить статистику прогресса для всех пользователей по курсу
     * (доступно только для администраторов)
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function getCourseProgressStats($courseId)
    {
        // Проверка прав доступа
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course = Course::findOrFail($courseId);
        $progress = Progress::where('course_id', $courseId)
                   ->with('user')
                   ->get();
        
        $stats = [
            'course' => $course->title,
            'total_users' => $progress->count(),
            'completed' => $progress->where('status', 'completed')->count(),
            'in_progress' => $progress->where('status', 'in_progress')->count(),
            'not_started' => $progress->where('status', 'not_started')->count(),
            'average_score' => $progress->avg('score'),
            'average_time_spent' => $progress->avg('time_spent'),
            'detailed_progress' => $progress
        ];

        return response()->json($stats);
    }

    /**
     * Сбросить прогресс пользователя по курсу
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function resetProgress($courseId)
    {
        $progress = Progress::where('user_id', Auth::id())
                    ->where('course_id', $courseId)
                    ->first();

        if (!$progress) {
            return response()->json(['message' => 'Progress not found'], 404);
        }

        $progress->update([
            'status' => 'not_started',
            'completed_topics' => json_encode([]),
            'completed_topics_count' => 0,
            'last_topic_id' => null,
            'last_position' => null,
            'score' => 0,
            'time_spent' => 0
        ]);

        ActivityLogger::log('reset progress', 'Пользователь сбросил прогресс по курсу: ' . $progress->course->title);

        return response()->json(['message' => 'Progress reset successfully', 'progress' => $progress]);
    }

    /**
     * Получить прогресс пользователя по всем курсам
     */
    public function getUserProgress()
    {
        $user_id = Auth::id();
        $progress = Progress::where('user_id', $user_id)
            ->with(['course' => function ($query) {
                $query->select('id', 'title', 'description', 'image', 'time');
            }])
            ->get();

        // Подсчитываем общую статистику
        $stats = [
            'total_courses' => $progress->count(),
            'completed_courses' => $progress->where('status', 'completed')->count(),
            'in_progress_courses' => $progress->where('status', 'in_progress')->count(),
            'not_started_courses' => $progress->where('status', 'not_started')->count(),
            'total_time_spent' => $progress->sum('time_spent'),
            'average_score' => $progress->avg('score'),
        ];

        return response()->json([
            'progress' => $progress,
            'stats' => $stats
        ]);
    }

    /**
     * Получить прогресс пользователя по конкретному курсу
     */
    public function getCourseProgress($courseId)
    {
        $user_id = Auth::id();
        $progress = Progress::where('user_id', $user_id)
            ->where('course_id', $courseId)
            ->with(['course' => function ($query) {
                $query->select('id', 'title', 'description', 'image', 'time');
                $query->with(['topics' => function ($query) {
                    $query->select('id', 'course_id', 'title', 'sort_order');
                    $query->orderBy('sort_order', 'asc');
                }]);
            }])
            ->first();

        if (!$progress) {
            return response()->json([
                'message' => 'Прогресс не найден',
            ], 404);
        }

        // Получаем общее количество тем в курсе
        $totalTopics = Topic::where('course_id', $courseId)->count();
        
        // Вычисляем процент завершения
        $completionPercent = $totalTopics > 0 
            ? round(($progress->completed_topics_count / $totalTopics) * 100) 
            : 0;

        return response()->json([
            'progress' => $progress,
            'total_topics' => $totalTopics,
            'completion_percent' => $completionPercent,
        ]);
    }

    /**
     * Отметить тему как завершенную
     */
    public function markTopicCompleted(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'topic_id' => 'required|exists:topics,id',
            'score' => 'nullable|numeric|min:0|max:100',
            'time_spent' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();
        $courseId = $request->course_id;
        $topicId = $request->topic_id;
        
        // Получаем или создаем запись о прогрессе
        $progress = Progress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
            
        if (!$progress) {
            $progress = Progress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'in_progress',
                'completed_topics' => [],
                'completed_topics_count' => 0
            ]);
        }
        
        // Отмечаем тему как завершенную
        $topic = Topic::findOrFail($topicId);
        $progress->markTopicCompleted($topicId);
        
        // Обновляем дополнительные данные
        if ($request->has('score')) {
            $progress->score = $request->score;
        }
        
        if ($request->has('time_spent')) {
            $progress->time_spent += $request->time_spent;
        }
        
        $progress->last_topic_id = $topicId;
        $progress->last_position = $request->position ?? null;
        $progress->save();
        
        // Записываем в активность завершение темы
        Activity::log('topic_completed', [
            'course_id' => $courseId,
            'course_name' => $progress->course->title,
            'topic_id' => $topicId,
            'topic_name' => $topic->title
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $progress,
            'message' => 'Тема успешно отмечена как завершенная'
        ]);
    }

    /**
     * Обновить позицию в теме
     */
    public function updatePosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'topic_id' => 'required|exists:topics,id',
            'position' => 'required|array',
            'time_spent' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();
        $courseId = $request->course_id;
        $topicId = $request->topic_id;
        
        // Получаем или создаем запись о прогрессе
        $progress = Progress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
            
        if (!$progress) {
            $progress = Progress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'in_progress',
                'completed_topics' => [],
                'completed_topics_count' => 0
            ]);
        }
        
        // Обновляем позицию и дополнительные данные
        $progress->last_topic_id = $topicId;
        $progress->last_position = $request->position;
        
        if ($request->has('time_spent')) {
            $progress->time_spent += $request->time_spent;
        }
        
        // Если статус еще 'not_started', меняем на 'in_progress'
        if ($progress->status === 'not_started') {
            $progress->status = 'in_progress';
        }
        
        $progress->save();
        
        return response()->json([
            'success' => true,
            'data' => $progress,
            'message' => 'Позиция успешно обновлена'
        ]);
    }

    /**
     * Получить статистику по прохождению курса
     */
    public function getCourseStats($courseId)
    {
        $course = Course::findOrFail($courseId);
        $totalUsers = Progress::where('course_id', $courseId)->count();
        $completedUsers = Progress::where('course_id', $courseId)
            ->where('status', 'completed')
            ->count();
        $inProgressUsers = Progress::where('course_id', $courseId)
            ->where('status', 'in_progress')
            ->count();
            
        $topCompletedTopics = Topic::where('course_id', $courseId)
            ->withCount(['completedBy' => function($query) {
                $query->whereNotNull('completed_topics');
            }])
            ->orderBy('completed_by_count', 'desc')
            ->limit(5)
            ->get();
            
        $averageScore = Progress::where('course_id', $courseId)
            ->whereNotNull('score')
            ->avg('score');
            
        $averageTimeSpent = Progress::where('course_id', $courseId)
            ->where('time_spent', '>', 0)
            ->avg('time_spent');
            
        return response()->json([
            'success' => true,
            'data' => [
                'course' => $course->title,
                'total_users' => $totalUsers,
                'completed_users' => $completedUsers,
                'in_progress_users' => $inProgressUsers,
                'completion_rate' => $totalUsers > 0 ? round(($completedUsers / $totalUsers) * 100, 2) : 0,
                'average_score' => round($averageScore ?? 0, 2),
                'average_time_spent' => round($averageTimeSpent ?? 0),
                'top_completed_topics' => $topCompletedTopics
            ]
        ]);
    }

    /**
     * Получить прогресс всех пользователей по курсу (для администратора)
     */
    public function getUsersProgressByCourse($courseId)
    {
        $progress = Progress::with(['user', 'course'])
            ->where('course_id', $courseId)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $progress
        ]);
    }

    /**
     * Обновить статус прохождения темы
     *
     * @param Request $request
     * @param int $courseId
     * @param int $topicId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTopicStatus(Request $request, $courseId, $topicId)
    {
        $request->validate([
            'completed' => 'required|boolean',
            'position' => 'nullable|array',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        $user_id = Auth::id();
        
        // Получаем или создаем запись о прогрессе
        $progress = Progress::firstOrCreate(
            ['user_id' => $user_id, 'course_id' => $courseId],
            ['status' => 'not_started']
        );

        // Обрабатываем статус завершения темы
        if ($request->completed) {
            $progress->markTopicAsCompleted($topicId);
            $message = 'Тема отмечена как завершенная';
        } else {
            $progress->markTopicAsIncomplete($topicId);
            $message = 'Отметка о завершении темы снята';
        }

        // Обновляем позицию и последнюю тему
        if ($request->has('position')) {
            $progress->last_position = $request->position;
            $progress->last_topic_id = $topicId;
        }

        // Добавляем время прохождения, если оно передано
        if ($request->has('time_spent') && $request->time_spent > 0) {
            $progress->time_spent += $request->time_spent;
        }

        // Обновляем статус курса
        $totalTopics = Topic::where('course_id', $courseId)->count();
        if ($progress->completed_topics_count >= $totalTopics && $totalTopics > 0) {
            $progress->status = 'completed';
        } elseif ($progress->completed_topics_count > 0) {
            $progress->status = 'in_progress';
        }

        $progress->save();

        // Логируем активность
        $topic = Topic::find($topicId);
        $course = Course::find($courseId);
        
        if ($request->completed) {
            Activity::log(
                'progress_topic_completed',
                'Тема "' . $topic->title . '" курса "' . $course->title . '" завершена'
            );
        }

        return response()->json([
            'message' => $message,
            'progress' => $progress,
            'completion_percent' => $progress->getCompletionPercentage(),
        ]);
    }

    /**
     * Обновить данные о прогрессе
     *
     * @param Request $request
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProgress(Request $request, $courseId)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['not_started', 'in_progress', 'completed'])],
            'score' => 'nullable|numeric|min:0|max:100',
            'last_topic_id' => 'nullable|exists:topics,id',
            'last_position' => 'nullable|array',
        ]);

        $user_id = Auth::id();
        
        $progress = Progress::firstOrCreate(
            ['user_id' => $user_id, 'course_id' => $courseId],
            ['status' => 'not_started']
        );

        // Обновляем поля, если они переданы в запросе
        if ($request->has('status')) {
            $progress->status = $request->status;
        }
        
        if ($request->has('score')) {
            $progress->score = $request->score;
        }
        
        if ($request->has('last_topic_id')) {
            $progress->last_topic_id = $request->last_topic_id;
        }
        
        if ($request->has('last_position')) {
            $progress->last_position = $request->last_position;
        }

        $progress->save();

        // Логируем активность
        $course = Course::find($courseId);
        if ($request->has('status') && $request->status === 'completed') {
            Activity::log(
                'progress_course_completed',
                'Курс "' . $course->title . '" завершен'
            );
        }

        return response()->json([
            'message' => 'Прогресс успешно обновлен',
            'progress' => $progress,
        ]);
    }

    /**
     * Получить статистику прогресса для администратора
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminStats(Request $request)
    {
        // Проверка прав администратора
        if (!Auth::user()->is_admin) {
            return response()->json([
                'message' => 'Доступ запрещен',
            ], 403);
        }

        $course_id = $request->input('course_id');
        $user_id = $request->input('user_id');
        $group_id = $request->input('group_id');

        $query = Progress::query()
            ->with(['user:id,fio,email', 'course:id,title']);

        // Фильтрация по курсу
        if ($course_id) {
            $query->where('course_id', $course_id);
        }

        // Фильтрация по пользователю
        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        // Фильтрация по группе
        if ($group_id) {
            $user_ids = DB::table('group_user')
                ->where('group_id', $group_id)
                ->pluck('user_id');
            $query->whereIn('user_id', $user_ids);
        }

        $progress = $query->get();

        // Вычисляем статистику
        $stats = [
            'total_entries' => $progress->count(),
            'completed' => $progress->where('status', 'completed')->count(),
            'in_progress' => $progress->where('status', 'in_progress')->count(),
            'not_started' => $progress->where('status', 'not_started')->count(),
            'average_score' => $progress->avg('score'),
            'total_time_spent' => $progress->sum('time_spent'),
        ];

        return response()->json([
            'progress' => $progress,
            'stats' => $stats
        ]);
    }
} 