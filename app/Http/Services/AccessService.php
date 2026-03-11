<?php

namespace App\Http\Services;

use App\Models\Course;
use App\Models\Group2learning;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccessService
{
    const STATUS_ACTIVE = 'active';

    const STATUS_PENDING = 'pending';

    const STATUS_COMPLETED = 'completed';

    const STATUS_PAUSED = 'paused';

    const STATUS_CANCELED = 'canceled';

    public function checkCourseAccess(string $hash): array
    {
        $course = Course::where('hash', $hash)->first();

        if (! $course) {
            Log::warning("Курс не найден по хешу: {$hash}");
            abort(404, 'Курс не найден');
        }

        $user = Auth::user();

        if (! $user) {
            Log::warning("Неавторизованный доступ к курсу: {$hash}");
            abort(401, 'Требуется авторизация');
        }

        if ($user->isAdmin() || $user->isInstructor()) {
            Log::info("Админ/инструктор получил доступ к курсу: {$course->title}");

            return [
                'course' => $course,
                'category_id' => null,
                'full_access' => true,
            ];
        }

        $enrollment = Group2learning::where('course_id', $course->id)
            ->whereHas('group', function ($query) use ($user) {
                $query->where('id', $user->group_id);
            })
            ->where('status', '!=', self::STATUS_CANCELED)
            ->first();

        if (! $enrollment) {
            Log::warning("Пользователь {$user->id} не имеет доступа к курсу: {$course->title}");
            abort(403, 'Нет доступа к этому курсу');
        }

        $enrollmentStatus = $this->getEnrollmentStatus($enrollment);

        if ($enrollmentStatus === self::STATUS_CANCELED) {
            Log::warning("Пользователь {$user->id} - запись отменена: {$course->title}");
            abort(403, 'Запись на курс отменена');
        }

        Log::info("Пользователь {$user->id} получил доступ к курсу: {$course->title}, категория: {$enrollment->category_id}, статус: {$enrollmentStatus}");

        return [
            'course' => $course,
            'category_id' => $enrollment->category_id,
            'full_access' => false,
            'enrollment_status' => $enrollmentStatus,
        ];
    }

    public function getUserCourses(bool $includeAllStatuses = false): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        if ($user->isAdmin() || $user->isInstructor()) {
            return Course::where('visible', true)
                ->with(['aircraft', 'categories'])
                ->get()
                ->toArray();
        }

        $query = Group2learning::whereHas('group', function ($query) use ($user) {
            $query->where('id', $user->group_id);
        });

        if (! $includeAllStatuses) {
            $query->where('status', self::STATUS_ACTIVE);
        }

        $enrollments = $query
            ->with(['course.aircraft', 'course.categories', 'category'])
            ->get();

        $courses = [];
        $today = Carbon::today();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->course) {
                $status = $this->getEnrollmentStatus($enrollment);

                $courses[] = [
                    'course' => $enrollment->course,
                    'enrollment_id' => $enrollment->id,
                    'category_id' => $enrollment->category_id,
                    'category' => $enrollment->category,
                    'study_from' => $enrollment->study_from,
                    'study_to' => $enrollment->study_to,
                    'teacher' => $enrollment->teacher,
                    'typeOfLesson' => $enrollment->typeOfLesson,
                    'status' => $enrollment->status,
                    'enrollment_status' => $status,
                    'is_accessible' => $status === self::STATUS_ACTIVE,
                ];
            }
        }

        return $courses;
    }

    public function getEnrollmentStatus(Group2learning $enrollment): string
    {
        if ($enrollment->status === self::STATUS_CANCELED) {
            return self::STATUS_CANCELED;
        }

        if ($enrollment->status === self::STATUS_COMPLETED) {
            return self::STATUS_COMPLETED;
        }

        if ($enrollment->status === self::STATUS_PAUSED) {
            return self::STATUS_PAUSED;
        }

        $today = Carbon::today();
        $studyFrom = Carbon::parse($enrollment->study_from);
        $studyTo = Carbon::parse($enrollment->study_to);

        if ($today->lt($studyFrom)) {
            return self::STATUS_PENDING;
        }

        if ($today->gt($studyTo)) {
            return self::STATUS_COMPLETED;
        }

        return self::STATUS_ACTIVE;
    }

    public function filterAukstructuresByCategory(int $courseId, ?int $categoryId): array
    {
        if (! $categoryId) {
            return [];
        }

        $aukstructures = \App\Models\Aukstructure::where('course_id', $courseId)
            ->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            })
            ->orWhereDoesntHave('categories')
            ->orderBy('parent_id')
            ->orderBy('id')
            ->get()
            ->toArray();

        return $aukstructures;
    }
}
