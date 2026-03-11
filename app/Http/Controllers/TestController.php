<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Models\User;
//use App\Models\Role;
use App\Models\Group2learning;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
//use App\Traits\HasRolesAndPermissions; // использование трейта


class TestController extends Controller
{
    public function test(Request $request)
    {
        return response("yes", 201);
    }
}
