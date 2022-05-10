<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Traits\RedirectsToHomepage;
use App\Http\Controllers\Controller;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
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
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        return $this->doLogout($request)->withHeaders([
            'Clear-Site-Data' => ['"cache"', '"cookies"'],
        ]);
    }

    /**
     * The user has logged out of the application.
     *
     * @return Response
     */
    public function showLoggedout(Request $request): HttpResponse
    {
        $next = $request->hasValidSignature() ? $request->input('next') : null;

        return Response::view('auth.logout', [
            'next' => $next,
        ]);
    }

    /**
     * The user has logged out of the application.
     */
    protected function loggedOut(Request $request)
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
