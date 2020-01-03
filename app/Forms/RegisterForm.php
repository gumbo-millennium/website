<?php

declare(strict_types=1);

namespace App\Forms;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Kris\LaravelFormBuilder\Form;

/**
 * Registration form
 *
 * @package App\Forms
 */
class RegisterForm extends Form
{
    private const DUMMY_NAMES = [
        ['John', null, 'Wick'],
        ['Willie', 'van', 'Oranje'],
        ['Robin', 'of', 'Loxley'],
        ['Knights', 'of', 'The Round Table'],
        ['John', null, 'Doe'],
    ];

    /**
     * Returns a placeholder name
     * @return array
     */
    public function getPlaceholderName(): array
    {
        $name = Arr::random(self::DUMMY_NAMES);
        $name[3] = Str::slug(implode(' ', $name), '.') . '@example.com';
        return $name;
    }
    /**
     * Builds the form
     */
    public function buildForm()
    {
        $dummyName = $this->getPlaceholderName();

        $this
            ->add('first_name', 'text', [
                'label' => 'Voornaam',
                'rules' => 'required|string|min:2',
                'attr' => [
                    'placeholder' => $dummyName[0],
                    'autocomplete' => 'given-name',
                    'autofocus' => true
                ],
            ])
            ->add('insert', 'text', [
                'label' => 'Tussenvoegsel',
                'rules' => 'nullable|string|min:2',
                'attr' => [
                    'placeholder' => $dummyName[1],
                    'autocomplete' => 'additional-name'
                ],
            ])
            ->add('last_name', 'text', [
                'label' => 'Achternaam',
                'rules' => 'required|string|min:2',
                'attr' => [
                    'placeholder' => $dummyName[2],
                    'autocomplete' => 'family-name'
                ],
            ])
            ->add('email', 'email', [
                'label' => 'E-mailadres',
                'rules' => 'required|email|unique:users,email',
                'attr' => [
                    'placeholder' => $dummyName[3],
                    'autocomplete' => 'email'
                ],
                'help_block' => [
                    // phpcs:disable Generic.Files.LineLength.TooLong
                    'text' => <<<HTML
                    Ben je lid van Gumbo? Typ hier dan het e-mailadres in dat bekend is bij het bestuur.
                    Je krijgt dan automatisch je lidstatus toegewezen.
                    HTML
                    // phpcs:enable Generic.Files.LineLength.TooLong
                ],
            ])
            ->add('password', 'password', [
                'label' => 'Wachtwoord',
                'rules' => 'required|string|min:10',
                'attr' => [
                    'placeholder' => sprintf('hunter%d', now()->year),
                    'autocomplete' => 'new-password',
                    'minlength' => '10'
                ],
                'help_block' => [
                    // phpcs:disable Generic.Files.LineLength.TooLong
                    'text' => '<strong>Minimaal 10 tekens</strong>. Probeer een beetje origineel te zijn (gebruik niet je Google wachtwoord, bijvoorbeeld)'
                    // phpcs:enable Generic.Files.LineLength.TooLong
                ],
            ])
            ->add('alias', 'text', [
                'label' => 'Alias',
                'rules' => [
                    'nullable',
                    'string',
                    'min:4',
                    'regex:^[a-z0-9][a-z0-9-]{2,}[a-z0-9]$',
                    'unique:users,alias',
                ],
                'error_messages' => [
                    // phpcs:disable Generic.Files.LineLength.TooLong
                    'alias.min' => 'Je alias moet minimaal 4 tekens lang zijn',
                    'alias.regex' => 'Je alias mag alleen bestaan uit kleine letters, cijfers en eventueel streepjes in het midden',
                    'alias.unique' => 'Deze alias is al in gebruik door een andere gebruiker.'
                    // phpcs:enable Generic.Files.LineLength.TooLong
                ],
                'help_block' => [
                    // phpcs:disable Generic.Files.LineLength.TooLong
                    'text' => <<<HTML
                    Kies een optionele nickname die wordt getoond in plaats van je voornaam.<br />
                    Je kan gebruik maken van kleine letters en nummers, en eventueel streepjes in het midden.
                    HTML,
                    // phpcs:enable Generic.Files.LineLength.TooLong
                ],
                'attr' => [
                    'autocomplete' => 'nickname',
                    'pattern' => '[a-z0-9][a-z0-9-]{2,}[a-z0-9]'
                ],
            ])
            ->add('accept_terms', 'checkbox', [
                'label' => 'Ik ga akkoord met de uitgebreide privacy policy van Gumbo Millennium',
                'rules' => 'required|accepted'
            ])
            ->add('submit', 'submit', [
                'label' => 'Registreren'
            ]);
    }
}
