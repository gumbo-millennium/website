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
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Kris\LaravelFormBuilder\FormBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Allows a user to specify grants.
 */
class GrantsController extends Controller
{
    public const GRANTS_FILE = 'assets/yaml/grants.yaml';

    /**
     * Returns customizable grants.
     *
     * @return Generator<Grant>
     */
    public static function getGrants(): Generator
    {
        $grantFile = resource_path(self::GRANTS_FILE);

        $grants = Yaml::parseFile($grantFile);

        foreach ($grants as $key => $grant) {
            yield new Grant(
                $key,
                Arr::get($grant, 'name'),
                str_replace(PHP_EOL, ' ', Arr::get($grant, 'description')),
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
