<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response as ResponseFacade;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */
    use VerifiesEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');

        SEOMeta::setTitle('E-mailadres valideren');
        SEOMeta::setRobots('noindex,nofollow');
    }

    /**
     * Redirect route after verification.
     */
    public function redirectTo(): string
    {
        if (request()->routeIs('verification.notice')) {
            return route('account.index');
        }

        return route('verification.notice');
    }

    /**
     * Resend the email verification notification.
     *
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();

            flash()->info(__('A fresh verification link has been sent to your email address.'));
        }

        return ResponseFacade::redirectTo($this->redirectPath());
    }
}
