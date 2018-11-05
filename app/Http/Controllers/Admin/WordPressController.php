<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\CorcelUser;
use Corcel\Model\Attachment as CorcelMedia;
use Corcel\Model\Option as CorcelOption;
use Corcel\Model\Page as CorcelPage;
use Corcel\Model\Post as CorcelPost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\UpdateWordPressUserJob;

class WordPressController extends Controller
{
    /**
     * Index page
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if the user has an account
        if ($user->wordpress === null) {
            UpdateWordPressUserJob::dispatch($user);
        }

        return view('admin.wordpress-login')->with([
            'user' => $user,
            'count' => [
                'pages' => CorcelPage::published()->count(),
                'posts' => CorcelPost::published()->count(),
                'media' => CorcelMedia::count(),
                'users' => CorcelUser::count()
            ]
        ]);
    }

    /**
     * Performs the creation of the user, the creation of the login
     * request and the forwarding to the WordPress admin.
     *
     * @param WordPressLoginRequest $request
     * @return Response
     */
    public function login(Request $request)
    {
        // Get WordPress root URL
        $url = CorcelOption::get('siteurl');
        $redirectUrl = rtrim($url, '/\\');

        // Forward to WordPress
        return redirect()->away("{$url}/wp-admin/");
    }
}
