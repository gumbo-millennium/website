<?php

declare(strict_types=1);

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

/**
 * Form to reset your password
 */
class ForgotPasswordForm extends Form
{
    /**
     * Builds the form
     */
    public function buildForm()
    {
        $this
            ->add('email', 'email', [
                'label' => 'E-mailadres',
                'rules' => 'required|email',
                'attr' => [
                    'autocomplete' => 'email',
                    'autofocus' => true,
                ],
            ])
            ->add('submit', 'submit', [
                'label' => __('Send Password Reset Link'),
            ]);
    }
}
