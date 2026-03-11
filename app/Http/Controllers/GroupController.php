<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Group2learning;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Group::with(['creator', 'users']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('groupname', 'like', "%{$search}%")
                  ->orWhere('groupdescription', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        $groups = $query->paginate(10);

        return response()->json($groups);
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
            \Log::info('Group create request received', ['data' => $request->all()]);
            
            $validated = $request->validate([
                'groupname' => 'required|string|max:255',
                'groupdescription' => 'required|string',
                'is_active' => 'boolean',
                'max_users' => 'nullable|integer|min:1',
                'settings' => 'nullable|array',
            ]);

            // Ensure we have created_by
            $validated['created_by'] = auth()->id() ?? 1; // Fallback to admin ID if not authenticated
            
            \Log::info('Validated group data', ['validated' => $validated]);
            
            // Create the group with explicit attributes
            $group = new Group();
            $group->groupname = $validated['groupname'];
            $group->groupdescription = $validated['groupdescription'];
            $group->is_active = $validated['is_active'] ?? true;
            $group->max_users = $validated['max_users'] ?? null;
            $group->settings = $validated['settings'] ?? [];
            $group->created_by = $validated['created_by'];
            $group->save();
            
            \Log::info('Group created successfully', ['group_id' => $group->id]);
            
            // Load relationships and return
            $group->load(['creator', 'users']);
            
            return response()->json($group, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating group', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to create group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        return response()->json($group->load(['creator', 'users']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'groupname' => 'sometimes|string|max:255',
            'groupdescription' => 'sometimes|string',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
            'max_users' => 'nullable|integer|min:1',
        ]);

        $group->update($validated);

        return response()->json($group->load(['creator', 'users']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        $group->delete();
        return response()->json(null, 204);
    }

    public function addUsers(Request $request, Group $group)
    {
        if (!$group->canAddUser()) {
            return response()->json(['message' => 'Group is full'], 422);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Log the received data for debugging
        \Log::info('Adding users to group', [
            'group_id' => $group->id,
            'user_ids' => $validated['user_ids']
        ]);

        $group->users()->attach($validated['user_ids']);

        return response()->json($group->load('users'));
    }

    public function removeUsers(Request $request, Group $group)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Log the received data for debugging
        \Log::info('Removing users from group', [
            'group_id' => $group->id,
            'user_ids' => $validated['user_ids']
        ]);

        $group->users()->detach($validated['user_ids']);

        return response()->json($group->load('users'));
    }

    public function updateSettings(Request $request, Group $group)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $group->update([
            'settings' => array_merge($group->settings ?? [], $validated['settings']),
        ]);

        return response()->json($group->settings);
    }
}
