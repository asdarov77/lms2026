<?php

namespace App\Http\Controllers;

use App\Models\Group2learning;
use App\Models\User;
// use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

// use App\Traits\HasRolesAndPermissions; // использование трейта

class AuthController extends Controller
{
    // use HasRolesAndPermissions;// использование трейта

    // public function __construct()
    // {
    //     //$this->middleware('throttle:3,1')->only('login');
    //     $this->middleware("auth:sanctum")->except(['login', 'register']);
    // }

    public function register(Request $request)
    {
        $fields = $request->validate([
            'fio' => 'required|string',
            'password' => 'required|string|min:3|confirmed',
            'group_id' => 'required|exists:groups,id',
            'role' => 'required|string|in:Администратор,Пользователь,Инструктор,Обучаемый',
            'email' => 'nullable|string|email',
            'phonenumber' => 'nullable|string',
            'organization' => 'nullable|string',
            'position' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'rank' => 'nullable|string',
            'spfere' => 'nullable|string',
            'specialization' => 'nullable|string',
        ]);

        // Создаем пользователя только с заполненными полями
        $userData = [
            'fio' => $fields['fio'],
            'password' => bcrypt($fields['password']),
            'group_id' => $fields['group_id'],
            'role' => $fields['role'],
        ];

        // Добавляем дополнительные поля, если они присутствуют в запросе
        foreach (['email', 'phonenumber', 'organization', 'position', 'city', 'country', 'rank', 'spfere', 'specialization'] as $field) {
            if (isset($fields[$field])) {
                $userData[$field] = $fields[$field];
            }
        }

        $user = User::create($userData);

        // Подгружаем связанную группу
        $user->load('group');

        return response(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'fio' => 'required|string',
            'password' => 'required|string|min:3',
        ], [
            'password.min' => 'Пароль должен содержать минимум 3 символа',
            'password.required' => 'Пароль обязателен для ввода',
        ]);

        $login = trim($fields['fio']);
        $user = User::query()
            ->where('fio', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user || ! Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'message' => 'Неверный логин или пароль',
            ], 401);
        }

        // Создаем токен
        $token = $user->createToken($request->fio)->plainTextToken;

        // Получаем разрешения
        $permissions = $user->permissions;

        // Авторизуем пользователя в сессии
        Auth::login($user);

        $response = [
            'token' => $token,
            'user' => $user,
            'permissions' => $permissions,
        ];

        Log::info('Login response:', $response);

        return response()->json($response, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); // удаляем все токены !

        return response('logout', 201);
    }

    public function destroy($id)
    {
        if ($id != 1) {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(null, 204);
        }

        return response()->json('невозможно удалить супер пользователя', 500);
    }

    // public function getUserList()
    // {

    //     //if(Gate::allow('view')){
    //     //if($this->hasPermission('manage-users'));
    //     if(Auth::user()->role = "Администратор")
    //     {
    //         $user = User::orderBy('id')->get();
    //     }
    //     else
    //     {
    //     $user = User::orderBy('id')
    //     ->where('group_id','=','Auth::user()->group_id')
    //     ->get();
    //     }
    //     foreach ($user as $_user) {
    //         //$_user->roles;
    //         $_user->group;
    //         $_user->permissions;
    //     }
    //     return $user;

    //     //return $this->hasPermission('manage-users');
    //     //}
    //     //else
    //     //return 'нет прав';
    // }

    public function getUserList()
    {
        if (Auth::user()->role == 'Администратор') {
            $user = User::orderBy('id')->get();
        } else {
            $user = User::orderBy('id')
                ->where('group_id', '=', Auth::user()->group_id)
                ->get();
        }
        foreach ($user as $_user) {
            // $_user->roles;
            $_user->group;
            $_user->permissions;
            // $_user->categories;

        }

        return $user;
    }

    public function getUser($id)
    {
        $user = User::findOrFail($id);

        // Подгружаем связанные данные
        $user->load('group');
        $user->load('permissions');

        // Логируем полученные данные для отладки
        \Log::info('Данные пользователя для редактирования', [
            'id' => $id,
            'user' => $user->toArray(),
        ]);

        return $user;
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'fio' => 'required|string',
            'role' => 'required|string|in:Администратор,Пользователь,Инструктор,Обучаемый',
            'phonenumber' => 'nullable|string',
            'email' => 'nullable|email',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'organization' => 'nullable|string',
            'position' => 'nullable|string',
            'rank' => 'nullable|string',
            'spfere' => 'nullable|string',
            'specialization' => 'nullable|string',
            'group_id' => 'required|exists:groups,id',
        ]);

        $user = User::findOrFail($id);

        // Log для отладки
        \Log::info('Обновление пользователя', [
            'id' => $id,
            'validated' => $validated,
            'request' => $request->all(),
        ]);

        $user->fill($validated);
        $user->save();

        if ($request->has('permission_id')) {
            $user->permissions()->sync($request->permission_id);
        }

        // Загружаем связанную группу для ответа
        $user->load('group');

        return response($user, 201);
    }

    public function chpass(Request $request, $id)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:3|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->password = bcrypt($validated['password']);
        $user->save();

        return response($user, 201);
    }

    public function chroll(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->roles()->sync($request->role_id);

        return response($user, 201);
    }

    public function chperm(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->permissions()->sync($request->permission_id);

        return response($user, 201);
    }

    public function group2learning(Request $request)
    {
        try {
            // Log the incoming request data
            \Log::info('Group2learning request received', ['data' => $request->all()]);

            // Validate the input
            $validated = $request->validate([
                'group_id' => 'required|exists:groups,id',
                'course_id' => 'required|array',
                'course_id.*' => 'required|exists:courses,id',
                'category_id' => 'nullable',
                'teacher' => 'required|string',
                'typeOfLesson' => 'required|string',
                'study_from' => 'required|date',
                'study_to' => 'required|date',
            ]);

            // Check if courses array is empty
            if (empty($request->course_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Необходимо выбрать хотя бы один курс',
                ], 422);
            }

            // Track inserted records
            $inserted = [];

            foreach ($request->course_id as $_course_id) {
                // Get the category_id from the course if not provided
                $categoryId = $request->category_id;
                if (empty($categoryId)) {
                    // Find the course
                    $course = \App\Models\Course::find($_course_id);
                    if ($course) {
                        $categoryId = $course->category_id;
                    }

                    // If still empty, use default category (1)
                    if (empty($categoryId)) {
                        $categoryId = 1; // Default category ID
                    }
                }

                $data = [
                    'group_id' => $request->group_id,
                    'category_id' => $categoryId,
                    'course_id' => $_course_id,
                    'teacher' => $request->teacher,
                    'typeOfLesson' => $request->typeOfLesson,
                    'study_from' => $request->study_from,
                    'study_to' => $request->study_to,
                ];

                \Log::info('Creating group2learning record', ['data' => $data]);

                // Use the model to create the record
                $learning = Group2learning::create($data);
                $inserted[] = $learning;
            }

            // Return successful response with the created records
            return response()->json([
                'success' => true,
                'message' => 'Группа успешно зарегистрирована на курс(ы)',
                'data' => $inserted,
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error in group2learning: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при регистрации группы на курс: '.$e->getMessage(),
            ], 500);
        }
    }

    // public function editData ($id)
    // {
    //     $user = User::find($id);

    //     $name = $user->name;
    //     $email = $user->email;
    //     //return view('useredit', [])
    //     //$id = Auth::user()->id;
    //     //$name = Auth::user()->name;
    //     //$email = Auth::user()->email;
    //     // return response() ->json([
    //     //     'status'=> 200,
    //     //     'data'=> $user,
    //     // ]);
    //     $data = [
    //         'id' => $id,
    //         'name' => $name,
    //         'email' => $email,
    //     ];
    //     return $data;
    // }

}
