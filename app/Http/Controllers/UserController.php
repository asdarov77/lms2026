<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['group', 'roles', 'permissions']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->paginate(10);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => ['required', Password::defaults()],
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'patronymic' => 'nullable|string|max:50',
            'phonenumber' => 'nullable|string|max:16',
            'city' => 'nullable|string|max:25',
            'country' => 'nullable|string|max:30',
            'organization' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'rank' => 'nullable|string|max:30',
            'spfere' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:100',
            'group_id' => 'nullable|exists:groups,id',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['fio'] = trim("{$validated['last_name']} {$validated['first_name']} {$validated['patronymic']}");

        $user = User::create($validated);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return response()->json($user->load(['group', 'roles', 'permissions']), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['group', 'roles', 'permissions']));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'patronymic' => 'nullable|string|max:50',
            'phonenumber' => 'nullable|string|max:16',
            'city' => 'nullable|string|max:25',
            'country' => 'nullable|string|max:30',
            'organization' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'rank' => 'nullable|string|max:30',
            'spfere' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:100',
            'group_id' => 'nullable|exists:groups,id',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        if ($request->has('first_name') || $request->has('last_name') || $request->has('patronymic')) {
            $validated['fio'] = trim("{$validated['last_name']} {$validated['first_name']} {$validated['patronymic']}");
        }

        $user->update($validated);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return response()->json($user->load(['group', 'roles', 'permissions']));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function changePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function updateSettings(Request $request, User $user)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $user->update([
            'settings' => array_merge($user->settings ?? [], $validated['settings']),
        ]);

        return response()->json($user->settings);
    }
} 