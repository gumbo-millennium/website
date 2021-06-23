<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Forms\AccountGrantsForm;
use App\Http\Controllers\Controller;
use App\Models\Grant;
use App\Models\User;
use Generator;
use Illuminate\Http\RedirectResponse as HttpRedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
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
    public function editGrants(FormBuilder $formBuilder, Request $request): HttpResponse
    {
        // Get current user
        $user = $request->user();
        \assert($user instanceof User);

        return Response::view('account.grants', [
            'user' => $user,
            'form' => $this->getForm($formBuilder, $user),
        ])->setPrivate();
    }

    /**
     * Applying the changes.
     */
    public function updateGrants(FormBuilder $formBuilder, Request $request): HttpRedirectResponse
    {
        // Get current user
        $user = $request->user();
        \assert($user instanceof User);

        // Make form
        $form = $this->getForm($formBuilder, $user);

        // Get values
        $formValues = $form->getFieldValues();

        // Apply new values
        foreach ($this->getGrants() as $grant) {
            $checked = $formValues[$grant->key];

            $user->setGrant($grant->key, (bool) $checked);
        }
        $user->save();

        // Flash OK
        flash('Je toestemmingen zijn bijgewerkt', 'success');

        return Response::redirectToRoute('account.index');
    }

    /**
     * Returns the form for modifying the grants.
     */
    private function getForm(FormBuilder $formBuilder, User $user): AccountGrantsForm
    {
        // Make form
        $form = $formBuilder->create(AccountGrantsForm::class, [
            'method' => 'POST',
            'url' => route('account.grants'),
            'model' => $user,
        ]);

        // Create form
        \assert($form instanceof AccountGrantsForm);

        return $form;
    }
}
