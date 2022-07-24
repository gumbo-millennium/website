<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Grant;
use App\Models\User;
use Generator;
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
     * Returns customizable grants.
     *
     * @return Generator<Grant>
     */
    public static function getGrants(): Generator
    {
        foreach (Config::get('gumbo.account.grants') as $key => $grant) {
            yield new Grant(
                $key,
                $grant['name'],
                str_replace(PHP_EOL, ' ', $grant['description']),
            );
        }
    }

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
    public function editGrants(Request $request): HttpResponse
    {
        // Get current user
        /** @var User $user */
        $user = $request->user();

        return Response::view('account.grants', [
            'user' => $user,
            'grants' => self::getGrants(),
        ]);
    }

    /**
     * Applying the changes.
     */
    public function updateGrants(FormBuilder $formBuilder, Request $request): HttpRedirectResponse
    {
        // Get current user
        /** @var User $user */
        $user = $request->user();
        $grants = Collection::make(self::getGrants());

        // Check the request
        $validValues = $request->only($grants->pluck('key'));

        // Apply new values
        foreach ($grants as $grant) {
            $checked = $validValues[$grant->key] ?? false;

            $user->setGrant($grant->key, (bool) $checked);
        }
        $user->save();

        // Flash OK
        flash()->success('Je toestemmingen zijn bijgewerkt');

        return Response::redirectToRoute('account.index');
    }
}
