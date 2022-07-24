<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Displays the password change form.
     */
    public function edit(): HttpResponse
    {
        return Response::view('account.password');
    }

    /**
     * Changes the password.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $valid = $request->validate([
            'current_password' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    if (! $user->password || ! Hash::check($value, $user->password)) {
                        return $fail(__('The current password is incorrect.'));
                    }
                },
            ],
            'new_password' => [
                'required',
                'string',
                'max:255',
                Password::min(8)->uncompromised(),
            ],
        ]);

        $user->forceFill([
            'password' => Hash::make($valid['new_password']),
        ])->save();

        $message = [__('Your password was changed succesfully.')];

        if ($request->input('logout')) {
            Auth::logoutOtherDevices($request->new_password);
            $message[] = __('You have been logged out of all other devices.');
        }

        flash()->success(implode(' ', $message));

        return Response::redirectToRoute('account.password.edit');
    }
}
