<?php

namespace App\Http\Controllers;

use App\Http\Filters\Group2learningFilter;
use App\Http\Requests\Group2learning\FilterRequest;
use Illuminate\Http\Request;
use App\Models\Group2learning;




class Group2learningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(FilterRequest $request)

    {    
            $data = $request->validated();
            $filter = app()->make(Group2learningFilter::class, ['queryParams' => array_filter($data)]);
            $learnings = Group2learning::filter($filter)->get();
            return $learnings;         
    }


    // public function index()

    // {
    //     $learnings=Group2learning::all();

    //     foreach($learnings as $_learning)        
    //     $_learning->course;

    //     return $_learning;
    // }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'group_id' => 'required|exists:groups,id',
                'course_id' => 'required|exists:courses,id',
                'category_id' => 'nullable|exists:categories,id',
                'parent_id' => 'nullable|integer',
                'teacher' => 'required|string',
                'typeOfLesson' => 'required|string',
                'study_from' => 'required|date',
                'study_to' => 'required|date'
            ]);
            
            // Create record
            $group2learning = Group2learning::create($validated);
            
            // Return response
            return response()->json([
                'success' => true,
                'message' => 'Запись на курс успешно создана',
                'data' => $group2learning
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('Error creating group2learning: ' . $e->getMessage(), [
                'file' => $e->getFile(), 
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при записи группы на курс: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $learnings = Group2learning::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'group_id' => 'required|exists:groups,id',
                'course_id' => 'required|exists:courses,id',
                'category_id' => 'nullable|exists:categories,id',
                'parent_id' => 'nullable|integer',
                'teacher' => 'required|string',
                'typeOfLesson' => 'required|string',
                'study_from' => 'required|date',
                'study_to' => 'required|date',
                'status' => 'nullable|string|in:active,completed,paused,canceled'
            ]);
            
            $group2learn = Group2learning::findOrFail($id);

            // Update fields from request
            $group2learn->course_id = $request->input('course_id');
            $group2learn->group_id = $request->input('group_id');
            $group2learn->category_id = $request->input('category_id');
            $group2learn->parent_id = $request->input('parent_id');
            $group2learn->teacher = $request->input('teacher');
            $group2learn->typeOfLesson = $request->input('typeOfLesson');
            $group2learn->study_from = $request->input('study_from');
            $group2learn->study_to = $request->input('study_to');
            
            // If status field exists in the model, update it
            if (isset($request->status) && schema_has_column('group2learnings', 'status')) {
                $group2learn->status = $request->input('status');
            }

            // Save changes to database
            $group2learn->save();

            // Return updated model
            return response()->json([
                'success' => true,
                'data' => $group2learn,
                'message' => 'Запись успешно обновлена'
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Error updating group2learning: ' . $e->getMessage(), [
                'file' => $e->getFile(), 
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении записи: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
            // Находим экземпляр модели по его id
    $group2learn = Group2learning::findOrFail($id);

    // Удаляем найденный экземпляр из базы данных
    $group2learn->delete();

    // Возвращаем ответ об успешном удалении ресурса
    return response()->json([
        'message' => 'Ресурс успешно удален'
    ], 200);
    }
}
