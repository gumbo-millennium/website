<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\File;
use App\JoinRequest;

/**
 * Admin homepage
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class HomeController extends Controller
{
    /**
     * Require being logged in
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show a homepage with some stats
     *
     * @param Request $request
     * @return Response
     */
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
                'change' => $allFiles === 0 ? 0 : $recentFiles / $allFiles * 100
            ],
            'users' => [
                'count' => $allUsers
            ]
        ]);
    }
}
