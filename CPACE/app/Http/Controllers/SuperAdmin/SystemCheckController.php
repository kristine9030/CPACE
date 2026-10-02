<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SystemCheckService;
use Illuminate\Http\Request;

class SystemCheckController extends Controller
{
    public function index(Request $request, SystemCheckService $checks)
    {
        return view('superadmin.system-checks', ['checks' => $checks->runAll(), 'ranAt' => now()]);
    }
}
