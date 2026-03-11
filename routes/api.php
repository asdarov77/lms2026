<?php

use App\Http\Controllers\AircraftController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ClearDBController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CoursesListController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FileLoadAndExtractController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\GradeBoundaryController;
use App\Http\Controllers\Group2learningController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\KlenImportController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrivateController;
use App\Http\Controllers\PrivateManiController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SearchController2;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserCoursesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes (public)
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Public Routes
Route::get('/test', [TestController::class, 'test']);
Route::get('/courses', [CoursesListController::class, 'getCourses']);
Route::get('/coursemanifest/{id}', [CourseController::class, 'showmanifest']);
Route::get('/getlink/{id}', [CourseController::class, 'getlink']);
Route::get('/getfirstauk/{id}', [CourseController::class, 'get_first_auk']);
Route::get('/classes', [AircraftController::class, 'indexclasses']);
Route::get('/classesfs', [AircraftController::class, 'showclassesfs']);
Route::post('/classes', [AircraftController::class, 'storeclasses']);
Route::get('/classess/{air}', [CourseController::class, 'showauks']);
Route::post('/city', [CityController::class, 'index']);
Route::post('/upload', [FileLoadAndExtractController::class, 'upload']);
Route::post('/extract', [FileLoadAndExtractController::class, 'extract']);

// Protected Routes (requires sanctum authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // User Management
    Route::post('/user/list', [AuthController::class, 'getUserList']);
    Route::get('/user/list', [AuthController::class, 'getUserList']);
    Route::get('/user/list/{id}', [AuthController::class, 'getUser']);
    Route::get('/user/{id}/edit', [AuthController::class, 'editData']);
    Route::put('/user/chpass/{id}', [AuthController::class, 'chpass']);
    Route::delete('/user/{id}', [AuthController::class, 'destroy']);
    Route::patch('/user/{id}', [AuthController::class, 'update']);
    Route::put('/user/chperm/{id}', [AuthController::class, 'chperm']);

    // Group and Learning
    Route::post('/group/learning', [AuthController::class, 'group2learning']);
    Route::apiResource('/learning', Group2learningController::class);
    Route::get('/lessons', [LessonsController::class, 'lessons']);

    // Course Management
    Route::apiResource('/course', CourseController::class);
    Route::apiResource('/categories', CategoryController::class);

    // Topics
    Route::get('/topics', function () {
        return \App\Models\Topic::all();
    });
    Route::put('/topics/{topic}', function (\Illuminate\Http\Request $request, \App\Models\Topic $topic) {
        $topic->update($request->all());

        return $topic;
    });
    Route::delete('/topics/{topic}', function (\App\Models\Topic $topic) {
        $topic->delete();

        return response()->json(['message' => 'Deleted']);
    });

    // Group Management - Explicit routes for better debugging
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    Route::put('/groups/{group}', [GroupController::class, 'update']);
    Route::delete('/groups/{group}', [GroupController::class, 'destroy']);

    // Group users management
    Route::post('/groups/{group}/add-users', [GroupController::class, 'addUsers']);
    Route::delete('/groups/{group}/remove-users', [GroupController::class, 'removeUsers']);

    // Files and Content
    Route::post('/files/add', [FilesController::class, 'upload']);
    Route::post('/search-files', [SearchController::class, 'search']);
    Route::post('/search-files2', [SearchController2::class, 'search']);
    Route::post('/get-content', [SearchController::class, 'get_file_content']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/add', [FavoriteController::class, 'fav_add']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'remove']);

    // Logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Progress Routes
    Route::get('/progress', [ProgressController::class, 'index']);
    Route::get('/progress/group/{id}', [ProgressController::class, 'getGroupProgress']);
    Route::get('/progress/student/{id}', [ProgressController::class, 'getStudentProgress']);
    Route::get('/progress/course/{id}', [ProgressController::class, 'getCourseProgress']);

    // Settings routes
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index']);
    Route::put('/settings', [App\Http\Controllers\SettingsController::class, 'update']);

    // Categories routes first (more specific)
    Route::get('/courses/cat', [App\Http\Controllers\CategoryListController::class, 'getCatCourses']);
    Route::get('/courses/cat/{id}', [App\Http\Controllers\CategoryListController::class, 'getCatCoursesID']);

    // Courses routes
    Route::get('/courses', [App\Http\Controllers\CourseController::class, 'index']);
    Route::post('/courses', [App\Http\Controllers\CourseController::class, 'store']);
    Route::get('/courses/{id}', [App\Http\Controllers\CourseController::class, 'show']);
    Route::put('/courses/{id}', [App\Http\Controllers\CourseController::class, 'update']);
    Route::delete('/courses/{id}', [App\Http\Controllers\CourseController::class, 'destroy']);
});

// Import Routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/import/aircrafts', [ImportController::class, 'getAircrafts']);
    Route::post('/import/run', [ImportController::class, 'import']);
    Route::post('/import/clear', [ImportController::class, 'clearDatabase']);

    // КЛЕН Import Routes
    Route::get('/klen/courses', [KlenImportController::class, 'index']);
    Route::post('/klen/import', [KlenImportController::class, 'import']);
    Route::post('/klen/import-all', [KlenImportController::class, 'importAll']);

    // Legacy Aircraft routes (from old project)
    Route::get('/classes', [AircraftController::class, 'indexclasses']);
    Route::get('/classesfs', [AircraftController::class, 'showclassesfs']);
    Route::post('/classes', [AircraftController::class, 'storeclasses']);
    Route::get('/classess/{air}', [CourseController::class, 'showauks']);
    Route::post('/upload', [FileLoadAndExtractController::class, 'upload']);
    Route::post('/extract', [FileLoadAndExtractController::class, 'extract']);
});

// Admin Routes (requires both authentication and admin role)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/role', RoleController::class);
    Route::apiResource('/permissions', PermissionController::class);
    Route::post('/clear-database', [ClearDBController::class, 'clear']);
    Route::post('/upload', [FileLoadAndExtractController::class, 'upload']);
    Route::post('/extract', [FileLoadAndExtractController::class, 'extract']);
    Route::apiResource('/grade-boundary', GradeBoundaryController::class);
    Route::apiResource('/settings', SettingsController::class);
});

// Private Content Routes with Hash (NEW - protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/private/{hash}/imsmanifest.xml', [PrivateManiController::class, 'xmles00']);
    Route::get('/private/{hash}/index.html', [PrivateController::class, 'htmles00']);
    Route::get('/private/{hash}/{html}', [PrivateController::class, 'htmles']);
    Route::get('/private/{hash}/{html}/{html2}', [PrivateController::class, 'htmles2']);
    Route::get('/private/{hash}/{html}/{html2}/{html3}', [PrivateController::class, 'htmles3']);
    Route::get('/private/{hash}/{html}/{html2}/{html3}/{html4}', [PrivateController::class, 'htmles4']);
    Route::get('/private/{hash}/{html}/{html2}/{html3}/{html4}/{html5}', [PrivateController::class, 'htmles5']);
    Route::get('/private/{hash}/{html}/{html2}/{html3}/{html4}/{html5}/{html6}', [PrivateController::class, 'htmles6']);
    Route::get('/private/{hash}/{html}/{html2}/{html3}/{html4}/{html5}/{html6}/{html7}', [PrivateController::class, 'htmles7']);
    Route::get('/private/{hash}/{html}/{html2}/{html3}/{html4}/{html5}/{html6}/{html7}/{html8}', [PrivateController::class, 'htmles8']);
});

// Legacy Private Content Routes (for backward compatibility)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/private/{aircraft}/{auk}/imsmanifest.xml', [PrivateManiController::class, 'xmles00']);
    Route::get('/private/{aircraft}/{auk}/index.html', [PrivateController::class, 'htmles00']);
    Route::get('/private/{aircraft}/{auk}/{html}', [PrivateController::class, 'htmles']);
    Route::get('/private/{aircraft}/{auk}/{html}/{html2}', [PrivateController::class, 'htmles2']);
    Route::get('/private/{aircraft}/{auk}/{html}/{html2}/{html3}', [PrivateController::class, 'htmles3']);
    Route::get('/private/{aircraft}/{auk}/{html}/{html2}/{html3}/{html4}', [PrivateController::class, 'htmles4']);
    Route::get('/private/{aircraft}/{auk}/{html}/{html2}/{html3}/{html4}/{html5}', [PrivateController::class, 'htmles5']);
    Route::get('/private/{aircraft}/{auk}/{html}/{html2}/{html3}/{html4}/{html5}/{html6}', [PrivateController::class, 'htmles6']);
    Route::get('/private/{aircraft}/{auk}/{html}/{html2}/{html3}/{html4}/{html5}/{html6}/{html7}', [PrivateController::class, 'htmles7']);
    Route::get('/private/{aircraft}/{auk}/{html}/{html2}/{html3}/{html4}/{html5}/{html6}/{html7}/{html8}', [PrivateController::class, 'htmles8']);
});

// Questions and GIFT Routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/gift', GiftController::class);
    Route::delete('/gift-clear', [GiftController::class, 'truncate']);
    Route::apiResource('/questions', QuestionsController::class);
});

// User Courses with Access Control
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-courses', [UserCoursesController::class, 'myCourses']);
});
