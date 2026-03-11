<?php

namespace App\Http\Controllers;

use App\Http\Services\AccessService;
use Illuminate\Http\Request;

class UserCoursesController extends Controller
{
    protected AccessService $accessService;

    public function __construct(AccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    public function myCourses(Request $request)
    {
        $includeAll = $request->boolean('include_all', false);
        $courses = $this->accessService->getUserCourses($includeAll);

        return response()->json($courses);
    }
}
