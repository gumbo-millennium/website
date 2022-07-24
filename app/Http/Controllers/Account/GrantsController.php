<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Grant;
use App\Models\User;
use Illuminate\Http\RedirectResponse as HttpRedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Kris\LaravelFormBuilder\FormBuilder;

/**
 * Allows a user to specify grants.
 */
class GrantsController extends Controller
{
    /**
     * Force auth.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Edit form.
     */
    public function edit(Request $request): HttpResponse
    {
        // Get current user
        /** @var User $user */
        $user = $request->user();

        return Response::view('account.grants', [
            'user' => $user,
            'grants' => Config::get('gumbo.account.grants'),
        ]);
    }

    /**
     * Applying the changes.
     */
    public function update(FormBuilder $formBuilder, Request $request): HttpRedirectResponse
    {
        // Get current user
        /** @var User $user */
        $user = $request->user();
        $inputValues = Config::get('gumbo.account.grants')
            ->mapWithKeys(fn (Grant $grant) => [
                $grant->key => (bool) $request->input($grant->key),
            ]);

        $user->grants = Collection::make($user->grants)
            ->merge($inputValues)
            ->reject(fn ($value) => $value === null);

        $user->save();

        // Flash OK
        flash()->success('Je toestemmingen zijn bijgewerkt');

        return Response::redirectToRoute('account.grants');
    }
}
