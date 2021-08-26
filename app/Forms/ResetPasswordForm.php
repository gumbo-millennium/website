<?php

declare(strict_types=1);

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

/**
 * Form to reset your password.
 */
class ResetPasswordForm extends Form
{
    /**
     * Builds the form.
     */
    public function buildForm()
    {
        $this
            ->add('token', 'hidden')
            ->add('email', 'email', [
                'label' => 'E-mailadres',
                'rules' => 'required|email',
                'attr' => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('password', 'password', [
                'label' => 'Nieuw wachtwoord',
                'rules' => 'required|min:10|confirmed',
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ])
            ->add('password_confirmation', 'password', [
                'label' => 'Bevestig wachtwoord',
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ])
            ->add('submit', 'submit', [
                'label' => __('Reset Password'),
            ]);
    }
}
