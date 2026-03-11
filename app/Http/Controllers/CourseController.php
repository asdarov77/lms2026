<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Aukstructure;
use App\Models\Category;
use App\Models\Course;
use App\Models\Link;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index($show)

    // вывод курсов. пока костыль. администратор видит все курсы,остальные только те на которые подписан пользователь
    // public function index()
    // {
    //     //$course = Course::where('visible',$show)->get();
    //     if (Auth::user()->role == "Администратор")
    //     {
    //         $cat = Category::all();
    //         foreach($cat as $item)
    //             $item->courses;
    //     }
    //     else
    //     {
    //         $temp = Auth::user()->categories;
    //         foreach ($temp as $_temp)
    //             $_temp->courses;
    //         return $temp;
    //     }
    //     return $cat;

    // }

    public function index(Request $request)
    {
        try {
            // Check user permissions
            if (! Auth::check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Filter by aircraft_id if provided
            $aircraftId = $request->get('aircraft_id');

            // Fetch courses based on user role
            if (Auth::user()->role === 'Администратор') {
                $query = Course::with(['categories']);
                if ($aircraftId) {
                    $query->where('aircraft_id', $aircraftId);
                }
                $courses = $query->get();
            } else {
                // For non-admin users, only show courses from their categories
                $userCategoryIds = Auth::user()->categories->pluck('id');
                $query = Course::whereHas('categories', function ($query) use ($userCategoryIds) {
                    $query->whereIn('categories.id', $userCategoryIds);
                })->with(['categories']);
                if ($aircraftId) {
                    $query->where('aircraft_id', $aircraftId);
                }
                $courses = $query->get();
            }

            // Add mock data for presentation if needed
            foreach ($courses as $course) {
                // Mock data for presentation
                $course->image = $course->image ?? 'https://picsum.photos/300/200?random='.$course->id;
                $course->students = $course->students ?? rand(10, 100);
                $course->progress = $course->progress ?? rand(0, 100);
                $course->status = $course->status ?? $this->getRandomStatus();
                $course->duration = $course->duration ?? $this->getRandomDuration();
            }

            // Log successful retrieval
            \Log::info('Courses fetched successfully', [
                'count' => $courses->count(),
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role,
            ]);

            return response()->json($courses);
        } catch (\Exception $e) {
            // Log error details
            \Log::error('Error fetching courses: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Failed to fetch courses: '.$e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|string',
                'duration' => 'required|string',
            ]);

            $course = Course::create($validated);

            // Assuming category is sent as the ID
            if ($request->has('category_id')) {
                $course->categories()->attach($request->category_id);
            }

            DB::commit();

            return response()->json($course->load('categories'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating course: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['message' => 'Failed to create course: '.$e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // -----------------------------рабочий вариант---------------------------------------------
    public function show($id)
    {
        try {
            $course = Course::with(['categories', 'aircraft', 'aukstructures.links'])->findOrFail($id);

            // Check if user has access to this course
            if (Auth::user()->role !== 'Администратор' &&
                ! $course->categories->pluck('id')->intersect(Auth::user()->categories->pluck('id'))->count()) {
                return response()->json(['message' => 'Unauthorized access to this course'], 403);
            }

            return response()->json($course);
        } catch (ModelNotFoundException $e) {
            Log::warning('Course not found: '.$id);

            return response()->json(['message' => 'Course not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching course: '.$e->getMessage());

            return response()->json(['message' => 'Failed to fetch course'], 500);
        }
    }

    // ----------------------------------------------------

    public function getlink($id)
    {
        try {
            $aukstructure = Aukstructure::findOrFail($id);
            $course = Course::findOrFail($aukstructure->course_id);
            $aircraft = Aircraft::findOrFail($course->aircraft_id);

            // Get the link for the current aukstructure
            $link = Link::where('aukstructure_id', $id)->value('link');

            // If no link found, get the first link for this course
            if (! $link) {
                $firstAukId = Aukstructure::where([
                    ['course_id', '=', $course->id],
                    ['type', '=', 3],
                ])->orderBy('id')->first();

                if (! $firstAukId) {
                    return response()->json(['message' => 'No valid links found'], 404);
                }

                $link = Link::where('aukstructure_id', $firstAukId->id)->value('link');
            }

            $url = Config::get('app.private_path').
                   $aircraft->path.'/'.
                   trim($course->path).'/'.
                   $link;

            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to get link'], 500);
        }
    }

    public function get_first_auk($auk_id)
    {
        try {
            $aukstructure = Aukstructure::findOrFail($auk_id);

            $firstAukId = Aukstructure::where([
                ['course_id', '=', $aukstructure->course_id],
                ['type', '=', 3],
                ['id', '>=', $auk_id],
            ])->orderBy('id')->first();

            if (! $firstAukId) {
                return response()->json(['message' => 'No valid aukstructure found'], 404);
            }

            return response()->json($firstAukId);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to get first auk'], 500);
        }
    }

    // -------------------------------------------------------

    // public function getlink($id)
    // {

    //     $link = (Link::where('aukstructure_id', $id)
    //         ->value('link')
    //         //->pluck('id')
    //         //->all()

    //     );
    //     $curCourse = Aukstructure::find($id)->where('type', 3)->firstOrFail();
    //     $curCourseId = $curCourse->id;

    //     $first = (Link::where('aukstructure_id', $curCourseId)
    //         ->value('link')
    //     );

    //     $aukstruct = Aukstructure::find($id);
    //     //$auks = Course::find($aukstruct->course_id);
    //     $auks = Course::where('id',  $aukstruct->course_id)
    //         ->first();
    //     //->value('path');
    //     $aircraft = Aircraft::find($auks->aircraft_id);

    //     $airpath = $aircraft->path;
    //     $aukspath = trim($auks->path);
    //     //dd($airpath);

    //     //$auks->path;
    //     // return json_encode($auks + $link);
    //     $url = Config::get('app.private_path') . $airpath . "/" . $aukspath . "/" . $link;
    //     $urlfirst = Config::get('app.private_path').$airpath ."/" .$aukspath."/".$first;
    //     //dd($aukspath,$airpath,$link,$urlfirst);
    //     // $response = [
    //     //     'url' => $url,
    //     //     'urlfirst' => $urlfirst,
    //     // ];
    //     //return $response;
    //     if($link!=null)
    //     return $url;
    //     else
    //     return $urlfirst;
    // }

    public function showmanifest($id)
    {
        $course = Course::find($id);
        $course->categories;
        $course->aircraft;
        // склейка пути
        //         $courses_path = Config::get('app.courses_path'); // usr/local/share
        //         $aircraft_path = trim($course->aircraft->path); // Ил-76
        //         $auk_path=trim($course->path);
        //         $full_path = $courses_path . '/' . $aircraft_path . '/' . $auk_path . '/index.html';
        // //        $file = readfile($full_path, $use_include_path = true);
        //         //$file = readfile($full_path);
        //         $content = file_get_contents($full_path ); // читаем содержимое файла в строку, readfile читает в буфер
        // // $content вставим функцию отправки файлов пунктов меню

        //         $response = [
        //               'content' => $content,
        //               'course' => $course,
        //               //'full_path' => $full_path,
        //           ];
        //          return response($response, 201);

        return $course;
    }
    // -----------------------------рабочий вариант---------------------------------------------

    // public  function showsublink (Request $request)
    // public  function showsublink ()
    // {
    // $course = request('file');
    // $coursesub = "/usr/local/share/courses/Ми-38/АУК-01/{$course}";

    // $content = file_get_contents($coursesub);
    // return $content;
    // return $course;

    // }

    // public function show11($id)
    // {
    //     $courses_path = '/usr/local/share/courses';
    //     $class_path = 'Ил-76';
    //     $courses = Course::find($id);
    //     $courses->categories;
    //     $auk_path = $courses_path . '/' . $class_path . '/' . trim($courses->path);
    //     //$temp = $auk_path . '/index.html';
    //     //return $temp;
    //     $file = readfile($auk_path . '/index.html');
    //     $parse = $this->parser(file_get_contents($auk_path . '/index.html'));

    //     $response = [
    //         'file' => $file,
    //         'parse' => $parse,
    //         'type' => gettype($file)
    //     ];
    //     return response($response, 201);
    //     //return readfile($auk_path . '/index.html');
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $course = Course::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|string',
                'duration' => 'required|string',
            ]);

            $course->update($validated);

            // Sync categories if provided
            if ($request->has('category_id')) {
                $course->categories()->sync($request->category_id);
            }

            DB::commit();

            return response()->json($course->load('categories'));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Course not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating course: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['message' => 'Failed to update course: '.$e->getMessage()], 500);
        }
    }

    // public function update(Request $request, $id)
    // {
    //     $courses = Course::find($id);

    //     $h_course = $courses->path_hash;
    //     $o_course = $courses->path;

    //     $courses->title = request('title');
    //     $courses->short_description = request('short_description');
    //     $courses->long_description = request('long_description');
    //     $courses->path = request('path');
    //     if($h_course == null)
    //     {
    //         //$hash = microtime();
    //         $h_course = $this->get_hash_course();
    //         $this->copy_and_hash_him($o_course,$h_course);
    //     }
    //     //$courses->visible = $request->visible;
    //     $courses->save();
    //     $courses->categories()->sync($request->category_id);
    //     //return response($courses,201);
    //     return response($courses, 201);
    //     //return "update ok, give my id";
    // }

    // ---------------------рабочий-------------------------------------------

    // public function update(Request $request, $id)
    // {
    //     $courses = Course::find($id);

    //     $h_course = $courses->path_hash;
    //     $o_course = $courses->path;
    //     // вариант с папкой,лежащей в файловой системе уровнями выше
    //     //  $o_course_folder = Config::get('app.courses_path_orig') . '/' . $o_course;
    //     // меняем  Config::get('app.courses_path_orig') на '../../../курсы/courses_data'
    //     // к которому конкатенируем '/' и затем название папки из БД поля 'path'
    //     // $o_course_folder_  с отрезанием первого символа у пути делать не надо
    //     // !! для копирования файлов силами PHP ( не laravel) путь должен иметь вид 'abc/xyz' вместо не '/abc/xyz'

    //     $o_course_folder = Config::get('app.courses_path_orig') . '/' . $o_course;
    //     $hash = uniqid();
    //     $h_course_folder = Config::get('app.courses_path_hashed') . '/' . $hash;
    //     $o_course_folder_ = trim(substr($o_course_folder, 1));
    //     $h_course_folder_ = trim(substr($h_course_folder, 1));

    //     $courses->title = request('title');
    //     $courses->short_description = request('short_description');
    //     $courses->long_description = request('long_description');
    //     $courses->path = request('path');
    //     //if ($h_course_folder_ == null) {
    //     if ($h_course == null && !is_dir($h_course_folder_)) {
    //         //$hash = microtime();
    //         //$ha_course = $this->get_hash_course();
    //         $this->recurseCopy($o_course_folder_, $h_course_folder_);
    //         $courses->path_hash = $hash;
    //         //$this->copy_and_hash_him($o_course,$ha_course);
    //     }
    //     //$courses->visible = $request->visible;
    //     $courses->save();
    //     $courses->categories()->sync($request->category_id);
    //     return response($courses, 201);
    // }
    // ---------------------рабочий-------------------------------------------

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $course = Course::findOrFail($id);
            $course->categories()->detach();
            $course->delete();

            DB::commit();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Course not found for deletion: '.$id);

            return response()->json(['message' => 'Course not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting course: '.$e->getMessage());

            return response()->json(['message' => 'Failed to delete course'], 500);
        }
    }
    // ----------------------------------------------------------

    /**
     * Helper method to get random status for mock data
     */
    private function getRandomStatus()
    {
        $statuses = ['В процессе', 'Завершен', 'Не начат', 'Приостановлен'];

        return $statuses[array_rand($statuses)];
    }

    /**
     * Helper method to get random duration for mock data
     */
    private function getRandomDuration()
    {
        $hours = rand(1, 10);
        $weeks = rand(1, 4);

        return rand(0, 1) ? "{$hours} часов" : "{$weeks} недель";
    }
}
