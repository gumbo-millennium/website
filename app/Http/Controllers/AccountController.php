<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Forms\AccountEditForm;
use App\Helpers\Str;
use App\Models\User;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilder;

/**
 * Allows a user to change it's account info
 */
class AccountController extends Controller
{
    /**
     * Index page
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return response()
            ->view('account.index', compact('user'))
            ->setPrivate();
    }

    /**
     * Edit form
     * @param FormBuilder $formBuilder
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function edit(FormBuilder $formBuilder, Request $request)
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
            'is-linked' => $isLinked
        ]);
        // Create form
        \assert($form instanceof AccountEditForm);

        return response()
            ->view('account.edit', [
                'user' => $user,
                'form' => $form,
                'isLinked' => $isLinked
            ])
            ->setPrivate();
    }

    /**
     * Applying the changes
     * @param FormBuilder $formBuilder
     * @param Request $request
     * @return void
     */
    public function update(FormBuilder $formBuilder, Request $request)
    {
        // Get current user
        $user = $request->user();
        \assert($user instanceof User);

        // Get some params
        $isLinked = $user->conscribo_id !== null;

        $form = $formBuilder->create(AccountEditForm::class, [
            'model' => $user,
            'user-id' => $user->id,
            'is-linked' => $isLinked
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
        if (!$isLinked) {
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

        // Flash oK
        flash($message, 'success');
        return response()
            ->redirectToRoute('account.index');
    }
}
