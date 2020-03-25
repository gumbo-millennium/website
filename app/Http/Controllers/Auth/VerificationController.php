<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Traits\RedirectsToHomepage;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Routing\Controller;

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
    use RedirectsToHomepage;

    /**
     * Create a new controller instance.
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
}
