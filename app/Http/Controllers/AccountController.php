<?php

namespace App\Http\Controllers;

use App\Forms\AccountEditForm;
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

        // Create form
        $form = $formBuilder->create(AccountEditForm::class, [
            'method' => 'PATCH',
            'url' => route('account.update'),
            'model' => $user,
        ]);

        return response()
            ->view('account.edit', compact('user', 'form'))
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

        // Get form
        $form = $formBuilder->create(AccountEditForm::class);

        // Or automatically redirect on error. This will throw an HttpResponseException with redirect
        $form->redirectIfNotValid();

        // Get values
        $userValues = $form->getFieldValues();

        // Keep track of changes
        $changeEmail = Str::lower($userValues['email']) !== $user->email;
        $changeAlias = Str::lower($userValues['alias']) !== $user->alias;

        // Check if e-mail was changed
        $
    }
}
