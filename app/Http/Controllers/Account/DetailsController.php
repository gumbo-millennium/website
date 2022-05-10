<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Forms\AccountEditForm;
use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse as HttpRedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Kris\LaravelFormBuilder\FormBuilder;

/**
 * Allows a user to change it's account info.
 */
class DetailsController extends Controller
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
    public function editDetails(FormBuilder $formBuilder, Request $request): HttpResponse
    {
        // Get current user
        $user = $request->user();
        \assert($user instanceof User);

        // Get some params
        $isLinked = $user->conscribo_id !== null;

        $form = $formBuilder->create(AccountEditForm::class, [
            'method' => 'PATCH',
            'url' => route('account.update'),
            'model' => $user,
            'user-id' => $user->id,
            'is-linked' => $isLinked,
        ]);
        // Create form
        \assert($form instanceof AccountEditForm);

        return Response::view('account.edit', [
            'user' => $user,
            'form' => $form,
            'isLinked' => $isLinked,
        ]);
    }

    /**
     * Applying the changes.
     */
    public function updateDetails(FormBuilder $formBuilder, Request $request): HttpRedirectResponse
    {
        // Get current user
        $user = $request->user();
        \assert($user instanceof User);

        // Get some params
        $isLinked = $user->conscribo_id !== null;

        $form = $formBuilder->create(AccountEditForm::class, [
            'model' => $user,
            'user-id' => $user->id,
            'is-linked' => $isLinked,
        ]);
        // Get form
        \assert($form instanceof AccountEditForm);

        // Set user
        $form->setUser($user);

        // Or automatically redirect on error. This will throw an HttpResponseException with redirect
        $form->redirectIfNotValid();

        // Get values
        $userValues = $form->getFieldValues();

        // Apply new values
        $user->email = Str::lower($userValues['email']);
        $user->alias = Str::lower($userValues['alias']);
        $user->syncChanges();

        // Flag e-mail as unverified, if changed
        if ($user->wasChanged('email')) {
            $user->email_verified_at = null;
        }

        // Update name, if allowed
        if (! $isLinked) {
            $user->first_name = $userValues['first_name'];
            $user->insert = $userValues['insert'];
            $user->last_name = $userValues['last_name'];
        }

        // Store changes
        $user->save();

        // Get long list of changes (for message)
        $allChanges = $user->getChanges();

        // Send notification
        $user->sendEmailVerificationNotification();

        // Change count
        $message = 'Je gegevens zijn bijgewerkt';
        if (empty($allChanges)) {
            $message = 'Je gegevens zijn niet aangepast.';
        }

        // Flash OK
        flash($message, 'success');

        return Response::redirectToRoute('account.index');
    }
}
