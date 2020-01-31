<?php

declare(strict_types=1);

namespace App\Forms;

use App\Forms\Traits\UserDataForm;
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
    use UserDataForm;

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
        $passwordPlaceholder = sprintf('hunter%d', now()->year);

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
            ->addEmail(null, [
                'attr.placeholder' => $dummyName[3],
            ])
            ->add('password', 'password', [
                'label' => 'Wachtwoord',
                'rules' => [
                    'required',
                    'string',
                    'min:10'
                ],
                'attr' => [
                    'placeholder' => $passwordPlaceholder,
                    'autocomplete' => 'new-password',
                    'minlength' => '10'
                ],
                'help_block' => [
                    // phpcs:disable Generic.Files.LineLength.TooLong
                    'text' => '<strong>Minimaal 10 tekens</strong>. Probeer een beetje origineel te zijn (gebruik niet je Google wachtwoord, bijvoorbeeld)'
                    // phpcs:enable Generic.Files.LineLength.TooLong
                ],
            ])
            ->addAlias()
            ->add('submit', 'submit', [
                'label' => 'Verder'
            ]);
    }
}
