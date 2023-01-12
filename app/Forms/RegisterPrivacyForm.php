<?php

declare(strict_types=1);

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

/**
 * Form to accept privacy policy.
 */
class RegisterPrivacyForm extends Form
{
    /**
     * Builds the form.
     */
    public function buildForm()
    {
        $this
            ->add('accept_terms', 'checkbox', [
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'label' => 'Ik begrijp en ga akkoord met het privacybeleid van Gumbo Millennium.',
                'rules' => 'required|accepted',
            ])
            ->add('submit', 'submit', [
                'label' => 'Account aanmaken',
            ]);
    }
}
