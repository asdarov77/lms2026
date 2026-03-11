<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Получаем статистику
            $stats = [
                'totalUsers' => User::count(),
                'totalCourses' => Course::count(),
                'totalGroups' => Group::count(),
                'totalAssignments' => 0, // Assignment table doesn't exist
            ];

            // Получаем последние активности
            $activities = Activity::latest()
                ->take(5)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'date' => $activity->created_at,
                        'color' => $this->getActivityColor($activity->type),
                    ];
                });

            // Получаем ближайшие дедлайны (empty - no assignments table)
            $deadlines = [];

            return response()->json([
                'stats' => $stats,
                'activities' => $activities,
                'deadlines' => $deadlines,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка при получении данных дашборда',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function getActivityColor($type)
    {
        return match ($type) {
            'user_created' => 'primary',
            'course_updated' => 'success',
            'assignment_created' => 'info',
            'test_completed' => 'warning',
            default => 'grey',
        };
    }
}
