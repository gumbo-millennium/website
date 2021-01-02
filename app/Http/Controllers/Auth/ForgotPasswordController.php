<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Forms\ForgotPasswordForm;
use App\Http\Controllers\Auth\Traits\RedirectsToHomepage;
use App\Http\Controllers\Controller;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Response;
use Kris\LaravelFormBuilder\FormBuilder;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */
    use SendsPasswordResetEmails;
    use RedirectsToHomepage;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        SEOMeta::setTitle('Wachtwoord herstellen');
        SEOMeta::setRobots('noindex,nofollow');
    }

    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm(FormBuilder $formBuilder): Response
    {
        $form = $formBuilder->create(ForgotPasswordForm::class, [
            'method' => 'POST',
            'url' => route('password.email'),
        ]);

        return response()
            ->view('auth.passwords.email', compact('form'));
    }
}
