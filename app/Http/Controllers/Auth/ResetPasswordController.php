<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Forms\ResetPasswordForm;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kris\LaravelFormBuilder\FormBuilder;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;
    use RedirectsToAdminHomeTrait;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display the form to request a password reset link.
     */
    public function showResetForm(Request $request, FormBuilder $formBuilder, $token = null): Response
    {
        $form = $formBuilder->create(ResetPasswordForm::class, [
            'method' => 'POST',
            'url' => route('password.update'),
            'model' => [
                'token' => $token,
                'email' => $request->email
            ]
        ]);

        return response()
            ->view('auth.passwords.reset', compact('form'));
    }
}
