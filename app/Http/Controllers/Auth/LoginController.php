<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Helpers\Str;
use App\Http\Controllers\Auth\Traits\RedirectsToHomepage;
use App\Http\Controllers\Controller;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers {
        logout as private doLogout;
    }
    use RedirectsToHomepage;

    private const LOGGED_IN_COOKIE_NAME = 'gumbo_logged_in';

    private const PURPOSES = [
        '/activiteiten/*/inschrijven/*' => 'Please login to enroll for this activity.',
        '/activiteiten' => 'Please login to view activities and members-only zones.',
        '/admin' => 'Please login to view the admin panel.',
        '/bestanden' => 'Please login to view the files.',
        '/gallery' => 'Please login to view the gallery.',
        '/mijn-account' => 'Please login to view your account.',
        '/plazacam' => 'Please login to view the Plazacam.',
        '/shop' => 'Please login to view the shop.',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Require guests
        $this->middleware('guest')->except('logout');

        // Add meta
        SEOMeta::setTitle('Inloggen');
        SEOMeta::setRobots('noindex,nofollow');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        $purpose = 'Get the most out of the Gumbo website, by logging into your account.';

        $storedNextUrl = Session::get('url.intended');
        $nextUrl = null;
        if ($storedNextUrl && Str::startsWith(URL::to($storedNextUrl), URL::to('/'))) {
            $nextUrl = Str::finish(parse_url(URL::to($storedNextUrl), PHP_URL_PATH), '/');

            foreach (self::PURPOSES as $path => $purpose) {
                if ($path === $nextUrl || Str::is($path, $nextUrl) || Str::startsWith($nextUrl, "{$path}/")) {
                    $purpose = $purpose;

                    break;
                }
            }
        }

        return Response::view('auth.login', [
            'purpose' => __($purpose),
            'seenBefore' => Request::hasCookie(self::LOGGED_IN_COOKIE_NAME),
        ])->setCache([
            'no_cache' => true,
            'no_store' => true,
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(HttpRequest $request)
    {
        return $this->doLogout($request)->withHeaders([
            'Clear-Site-Data' => ['"cache"', '"executionContexts"', '"storage"'],
        ]);
    }

    /**
     * The user has logged out of the application.
     *
     * @return Response
     */
    public function showLoggedout(HttpRequest $request): HttpResponse
    {
        $next = $request->hasValidSignature() ? $request->input('next') : null;

        return Response::view('auth.logout', [
            'next' => $next,
        ]);
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(HttpRequest $request, $user)
    {
        // Check if the user isn't locked
        if ($user->isLocked()) {
            $this->guard()->logout();

            $request->session()->invalidate();

            flash()->error(__('Your account has been locked. Please contact the board to unlock your account.'));

            return Response::redirectToRoute('login');
        }

        // Account is valid, write that the user has logged in before.
        Cookie::queue(self::LOGGED_IN_COOKIE_NAME, '1', Date::now()->addMonth()->diffInMinutes());

        // Do nothing else
    }

    /**
     * The user has logged out of the application.
     */
    protected function loggedOut(HttpRequest $request)
    {
        $previousUrl = $request->input('next');

        if (! $previousUrl) {
            return Response::redirectTo(URL::temporarySignedRoute('logout.done', Date::now()->addMinute()));
        }

        $previousPath = parse_url($previousUrl, PHP_URL_PATH);
        $previousHost = parse_url($previousUrl, PHP_URL_HOST);
        if (! $previousPath) {
            return Response::redirectTo(URL::temporarySignedRoute('logout.done', Date::now()->addMinute()));
        }

        $cleanUrl = url($previousPath);
        $cleanHost = parse_url($cleanUrl, PHP_URL_HOST);

        // Host is mismatching, abort!
        if ($cleanHost !== $previousHost) {
            return Response::redirectTo(URL::temporarySignedRoute('logout.done', Date::now()->addMinute()));
        }

        // Show logout page
        return Response::redirectTo(URL::temporarySignedRoute('logout.done', Date::now()->addMinute(), [
            'next' => $previousUrl,
        ]));
    }
}
