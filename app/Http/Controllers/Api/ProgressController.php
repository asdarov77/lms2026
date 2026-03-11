<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Course;
use App\Models\Group2learning;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Progress;
use App\Models\Topic;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Get overall progress statistics
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin sees all progress stats
        if ($user->isAdmin()) {
            $totalUsers = User::count();
            $totalCourses = Course::count();
            $completedCourses = Progress::where('status', 'completed')->count();
            $inProgressCourses = Progress::where('status', 'in_progress')->count();
            
            return response()->json([
                'total_users' => $totalUsers,
                'total_courses' => $totalCourses,
                'completed_courses' => $completedCourses,
                'in_progress_courses' => $inProgressCourses,
                'completion_rate' => $totalCourses > 0 ? round(($completedCourses / $totalCourses) * 100, 2) : 0
            ]);
        }
        
        // Regular user sees own progress
        return $this->getStudentProgress($user->id);
    }

    /**
     * Get progress for a specific group
     */
    public function getGroupProgress($id)
    {
        $group = Group::findOrFail($id);
        
        // Check permission
        $user = Auth::user();
        if (!$user->isAdmin() && $user->id !== $group->created_by) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $users = $group->users;
        $userProgress = [];
        
        foreach ($users as $user) {
            $progress = Progress::where('user_id', $user->id)->get();
            $totalTopics = 0;
            $completedTopics = 0;
            
            foreach ($progress as $p) {
                $course = Course::find($p->course_id);
                if ($course) {
                    $topicsCount = Topic::where('course_id', $course->id)->count();
                    $totalTopics += $topicsCount;
                    $completedTopics += ($p->completed_topics_count ?: 0);
                }
            }
            
            $userProgress[] = [
                'user_id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'progress_percent' => $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100, 2) : 0,
                'courses_completed' => Progress::where('user_id', $user->id)->where('status', 'completed')->count(),
                'courses_in_progress' => Progress::where('user_id', $user->id)->where('status', 'in_progress')->count()
            ];
        }
        
        return response()->json([
            'group_name' => $group->groupname,
            'user_count' => count($users),
            'users' => $userProgress
        ]);
    }

    /**
     * Get progress for a specific student
     */
    public function getStudentProgress($id)
    {
        $user = User::findOrFail($id);
        
        // Check permission
        $currentUser = Auth::user();
        if (!$currentUser->isAdmin() && $currentUser->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $progress = Progress::where('user_id', $user->id)->get();
        $courseProgress = [];
        
        foreach ($progress as $p) {
            $course = Course::find($p->course_id);
            if ($course) {
                $topicsCount = Topic::where('course_id', $course->id)->count();
                $completedTopicsCount = $p->completed_topics_count ?: 0;
                
                $courseProgress[] = [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'status' => $p->status,
                    'progress_percent' => $topicsCount > 0 ? round(($completedTopicsCount / $topicsCount) * 100, 2) : 0,
                    'completed_topics' => $completedTopicsCount,
                    'total_topics' => $topicsCount,
                    'last_accessed' => $p->updated_at
                ];
            }
        }
        
        return response()->json([
            'user_id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'courses' => $courseProgress,
            'total_courses' => count($courseProgress),
            'completed_courses' => count(array_filter($courseProgress, function($item) {
                return $item['status'] === 'completed';
            }))
        ]);
    }

    /**
     * Get progress stats for a specific course
     */
    public function getCourseProgress($id)
    {
        $course = Course::findOrFail($id);
        
        // Check permission
        $user = Auth::user();
        if (!$user->isAdmin() && $user->id !== $course->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $totalStudents = Progress::where('course_id', $course->id)->count();
        $completedCount = Progress::where('course_id', $course->id)->where('status', 'completed')->count();
        $inProgressCount = Progress::where('course_id', $course->id)->where('status', 'in_progress')->count();
        $notStartedCount = $totalStudents - $completedCount - $inProgressCount;
        
        $topicStats = [];
        $topics = Topic::where('course_id', $course->id)->get();
        
        foreach ($topics as $topic) {
            $completionRate = Progress::where('course_id', $course->id)
                ->whereJsonContains('completed_topics', $topic->id)
                ->count();
                
            $topicStats[] = [
                'topic_id' => $topic->id,
                'topic_name' => $topic->name,
                'completion_rate' => $totalStudents > 0 ? round(($completionRate / $totalStudents) * 100, 2) : 0
            ];
        }
        
        return response()->json([
            'course_id' => $course->id,
            'course_name' => $course->name,
            'total_students' => $totalStudents,
            'completion_stats' => [
                'completed' => $completedCount,
                'in_progress' => $inProgressCount,
                'not_started' => $notStartedCount
            ],
            'completion_rate' => $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100, 2) : 0,
            'topic_stats' => $topicStats
        ]);
    }

    /**
     * Метод для получения активности по дням недели
     */
    private function getWeeklyActivity($startDate)
    {
        // В реальном приложении здесь будет запрос к базе данных
        // Сейчас возвращаем фиктивные данные
        return [
            ['name' => 'Понедельник', 'value' => 75, 'color' => 'primary'],
            ['name' => 'Вторник', 'value' => 85, 'color' => 'success'],
            ['name' => 'Среда', 'value' => 60, 'color' => 'info'],
            ['name' => 'Четверг', 'value' => 90, 'color' => 'warning'],
            ['name' => 'Пятница', 'value' => 45, 'color' => 'error'],
            ['name' => 'Суббота', 'value' => 20, 'color' => 'grey'],
            ['name' => 'Воскресенье', 'value' => 15, 'color' => 'grey-darken-1']
        ];
    }

    /**
     * Метод для расчета общего прогресса
     */
    private function calculateOverallProgress($users, $courseId = null)
    {
        // В реальном приложении здесь будет запрос к базе данных
        // Сейчас возвращаем случайное значение
        return rand(55, 85);
    }

    /**
     * Получить прогресс по группам
     */
    private function getGroupsProgress($courseId = null)
    {
        $groups = Group::with('users')->get();
        
        return $groups->map(function ($group) use ($courseId) {
            $studentsProgress = $group->users->map(function ($user) use ($courseId) {
                if ($courseId) {
                    return $this->calculateStudentCourseProgress($user, $courseId);
                }
                return $this->calculateStudentProgress($user);
            });
            
            return [
                'id' => $group->id,
                'groupname' => $group->groupname,
                'student_count' => $group->users->count(),
                'progress' => $studentsProgress->avg() ?: rand(0, 100)
            ];
        });
    }

    /**
     * Получить прогресс по студентам
     */
    private function getStudentsProgress($users, $courseId = null)
    {
        return $users->map(function ($user) use ($courseId) {
            $progress = $courseId ? 
                $this->calculateStudentCourseProgress($user, $courseId) : 
                $this->calculateStudentProgress($user);
            
            return [
                'id' => $user->id,
                'fio' => $user->fio,
                'group_id' => $user->group_id,
                'progress' => $progress,
                'last_activity' => $this->getLastActivity($user)
            ];
        });
    }

    /**
     * Получить прогресс по курсам
     */
    private function getCoursesProgress($groupId = null)
    {
        $courses = Course::all();
        
        return $courses->map(function ($course) use ($groupId) {
            $usersQuery = User::query();
            
            if ($groupId) {
                $usersQuery->where('group_id', $groupId);
            }
            
            $users = $usersQuery->get();
            $studentsProgress = $users->map(function ($user) use ($course) {
                return $this->calculateStudentCourseProgress($user, $course->id);
            });
            
            return [
                'id' => $course->id,
                'title' => $course->title,
                'category_id' => $course->category_id,
                'progress' => $studentsProgress->avg() ?: rand(0, 100)
            ];
        });
    }

    /**
     * Рассчитать прогресс студента по курсу
     */
    private function calculateStudentCourseProgress($user, $courseId)
    {
        // В реальном приложении здесь будет запрос к таблице прогресса или тестам
        // Сейчас возвращаем случайное значение
        return rand(0, 100);
    }

    /**
     * Рассчитать общий прогресс студента
     */
    private function calculateStudentProgress($user)
    {
        // В реальном приложении здесь будет запрос средних значений прогресса по всем курсам
        // Сейчас возвращаем случайное значение
        return rand(0, 100);
    }

    /**
     * Получить последнюю активность студента
     */
    private function getLastActivity($user)
    {
        // В реальном приложении здесь будет запрос к таблице активностей
        // Сейчас возвращаем случайную дату за последний месяц
        $days = rand(0, 30);
        return Carbon::now()->subDays($days)->toIso8601String();
    }

    /**
     * Получить последнюю активность студента по курсу
     */
    private function getLastActivityForCourse($user, $courseId)
    {
        // В реальном приложении здесь будет запрос к таблице активностей с фильтром по курсу
        // Сейчас возвращаем случайную дату за последний месяц
        $days = rand(0, 30);
        return Carbon::now()->subDays($days)->toIso8601String();
    }

    /**
     * Подсчитать количество завершивших студентов
     */
    private function countCompletedStudents($users, $courseId = null)
    {
        if ($courseId) {
            return $users->filter(function ($user) use ($courseId) {
                return $this->calculateStudentCourseProgress($user, $courseId) >= 100;
            })->count();
        }
        
        return $users->filter(function ($user) {
            return $this->calculateStudentProgress($user) >= 100;
        })->count();
    }

    /**
     * Подсчитать количество студентов в процессе обучения
     */
    private function countInProgressStudents($users, $courseId = null)
    {
        if ($courseId) {
            return $users->filter(function ($user) use ($courseId) {
                $progress = $this->calculateStudentCourseProgress($user, $courseId);
                return $progress > 0 && $progress < 100;
            })->count();
        }
        
        return $users->filter(function ($user) {
            $progress = $this->calculateStudentProgress($user);
            return $progress > 0 && $progress < 100;
        })->count();
    }

    /**
     * Подсчитать количество не начавших студентов
     */
    private function countNotStartedStudents($users, $courseId = null)
    {
        if ($courseId) {
            return $users->filter(function ($user) use ($courseId) {
                return $this->calculateStudentCourseProgress($user, $courseId) == 0;
            })->count();
        }
        
        return $users->filter(function ($user) {
            return $this->calculateStudentProgress($user) == 0;
        })->count();
    }

    /**
     * Получить начальную дату для периода
     */
    private function getStartDateByPeriod($period)
    {
        $now = Carbon::now();
        
        return match ($period) {
            'week' => $now->subWeek(),
            'month' => $now->subMonth(),
            'quarter' => $now->subQuarter(),
            'year' => $now->subYear(),
            default => $now->subMonth()
        };
    }

    /**
     * Получить прогресс по курсам студента
     */
    private function getStudentCoursesProgress($user)
    {
        // В реальном приложении здесь будет запрос к таблице enrollments или назначений
        // Сейчас возвращаем все курсы с рандомным прогрессом
        $courses = Course::all();
        
        return $courses->map(function ($course) use ($user) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'progress' => $this->calculateStudentCourseProgress($user, $course->id),
                'last_activity' => $this->getLastActivityForCourse($user, $course->id)
            ];
        });
    }
} 