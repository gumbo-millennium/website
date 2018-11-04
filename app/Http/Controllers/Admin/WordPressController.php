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
use Illuminate\Support\Str;
use App\Http\Requests\WordPressLoginRequest;

class WordPressController extends Controller
{
    /**
     * Permissions required for the given WordPress scope
     *
     * @var array
     */
    const ACTION_PERMISSION_MAP = [
        'user' => 'content',
        'admin' => 'content-admin'
    ];

    /**
     * Maps permissions to the corresponding role and level in WordPress,
     * from low to high
     *
     * @var array
     */
    const PERMISSION_ROLE_LEVEL_MAP = [
        'content' => ['contributor', 1],
        'content-publish' => ['author', 2],
        'content-all' => ['editor', 7],
        'content-admin' => ['administrator', 9]
    ];

    /**
     * Index page
     *
     * @return Response
     */
    public function index(User $user)
    {
        // Send a list of users if the user is allowed to be admin
        if ($user->hasPermissionTo('content-admin')) {
            $users = CorcelUser::query()
                ->where('user_login', '!=', 'gumbo')
                ->get(['ID', 'user_login', 'display_name']);
        } else {
            $users = [];
        }

        return view('admin.wordpress-login')->with([
            'wp_users' => $users,
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
    public function login(WordPressLoginRequest $request)
    {
        // Get request fields
        $role = $request->role;

        // Get request user
        $user = $request->user();

        if ($request->has('as') && $user->hasPermissionTo('content-admin')) {
            // Find user by ID
            $wpUser = CorcelUser::findOrFail($request->as);
        } else {
            // Get WordPress user
            $wpUser = $this->getWordPressUser($user);
        }

        // Create login token
        list($requestId, $requestHash) = $this->createLoginToken($request, $wpUser);

        // Build WordPress login URL
        $url = CorcelOption::get('siteurl');
        $redirectUrl = rtrim($url, '/\\');
        $redirectParams = http_build_query([
            'login-request' => $requestId,
            'login-verify' => $requestHash
        ]);

        // Forward to WordPress
        return redirect()->away(sprintf(
            '%s/wp-login.php?%s',
            $redirectUrl,
            $redirectParams
        ));
    }

    /**
     * Returns the WordPress user for the given user
     *
     * @param User $user
     * @return CorcelUser
     */
    public function getWordPressUser(User $user) : CorcelUser
    {
        $wordpressUser = $user->wordpress_account;

        // No user, create one
        if ($wordpressUser === null) {
            $wordpressUser = $this->createWordPressUser($user);
        }

        // Determine correct role and level
        $assignedRole = 'subscriber';
        $assignedLevel = 0;

        // Loop through map
        foreach (self::PERMISSION_ROLE_LEVEL_MAP as $permission => list($role, $level)) {
            if ($user->hasPermissionTo($permission)) {
                // Roles are ordered by power, so as long as this is true, we're increasing the rank
                $assignedRole = $role;
                $assignedLevel = $level;
            } else {
                // But one failure and we stop
                break;
            }
        }

        // Assign correct role
        $wordpressUser->saveMeta([
            'wp_capabilities' => serialize([$assignedRole]),
            'wp_user_level' => $assignedLevel
        ]);

        // Return the user
        return $wordpressUser;
    }

    /**
     * Creates a new user with a random password
     *
     * @param User $user
     * @return CorcelUser
     */
    protected function createWordPressUser(User $user) : CorcelUser
    {
        // Assign user to account
        $wordpressUser = $user->wordpressAccount()->firstOrCreate([
            'user_login' => $user->email,
        ], [
            // E-mail from account
            'user_email' => $user->email,

            // Names
            'nice_name' => str_slug($user->name),
            'display_name' => $user->name,
        ]);

        // Generate a password if none is set
        if ($wordpressUser->user_pass === null) {
            $wordpressUser->generatePassword(20, 40);
        }

        // Store user and WordPress user
        $user->save();
        $wordpressUser->save();

        // return user
        return $wordpressUser;
    }

    /**
     * Builds a login request, includes message signing.
     *
     * @param Request $request
     * @param CorcelUser $user
     * @return array
     */
    protected function createLoginToken(Request $request, CorcelUser $user) : array
    {
        // Generate login ID
        $requestId = (string) Str::uuid();

        // Generate login ID secret
        $requestSecret = Str::random(40);

        $requestData = [
            // Random request ID
            'uuid' => $requestId,

            // User Id to login as
            'user' => $user->ID,

            // Ip address to allow login from
            'ip' => $request->ip(),

            // Window to login, make it tight
            'window' => [
                'start' => time(),
                'end' => time() + 5 * 60
            ]
        ];

        // Store login ID and secret
        CorcelOption::add("__login-{$requestId}", $requestData);
        CorcelOption::add("__login-{$requestId}-secret", $requestSecret);

        // Generate hash
        $requestHash = hash_hmac('sha256', json_encode($requestData), $requestSecret);

        // Return data
        return [$requestId, $requestHash];
    }
}
