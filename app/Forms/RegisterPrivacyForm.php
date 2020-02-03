<?php

declare(strict_types=1);

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

/**
 * Form to accept privacy policy
 * @package App\Forms
 */
class RegisterPrivacyForm extends Form
{
    /**
     * Builds the form
     */
    public function buildForm()
    {
        $this
            ->add('accept_terms', 'checkbox', [
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'label' => 'Ik ga akkoord met de privacy policy van Gumbo Millennium en begrijp de impact hiervan op mijn privacy.',
                'rules' => 'required|accepted'
            ])
            ->add('submit', 'submit', [
                'label' => 'Registreren'
            ]);
    }
}
