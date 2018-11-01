<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Corcel\Model\User;
use App\File;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Get stats for files
        $allFiles = File::count();
        $recentFiles = File::where('created_at', '>', now()->subDays(2))->count();

        // Get stats for users
        $allUsers = User::count();

        return view('admin.home')->with([
            'user' => $request->user(),
            'files' => [
                'count' => $allFiles,
                'change' => $recentFiles / $allFiles * 100
            ],
            'users' => [
                'count' => $allUsers
            ]
        ]);
    }
}
