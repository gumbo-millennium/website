<?php

declare(strict_types=1);

namespace App\Forms\Traits;

use App\Helpers\Arr;
use Illuminate\Validation\Rule;

/**
 * Registration form.
 */
trait UserDataForm
{
    /**
     * Add email field.
     *
     * @return Kris\LaravelFormBuilder\Form|UserDataForm
     */
    public function addEmail(?int $userId = null, array $override = []): self
    {
        // Unique rule
        $duplicateRule = Rule::unique('users', 'email');
        if ($userId) {
            logger()->debug('Adding {userId} to ignore list', compact('userId'));
            $duplicateRule = $duplicateRule->ignore($userId);
        }

        // Options
        $options = [
            'label' => 'E-mailadres',
            'rules' => [
                'required',
                'string',
                'email',
                $duplicateRule,
            ],
            'error_messages' => [
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'alias.unique' => 'Er bestaat al een account met dit e-mailadres.',
            ],
            'attr' => [
                'autocomplete' => 'email',
            ],
        ];

        return $this->add('email', 'email', $this->dotMerge($options, $override));
    }

    /**
     * Add alias.
     *
     * @return Kris\LaravelFormBuilder\Form|UserDataForm
     */
    public function addAlias(?int $userId = null, array $override = []): self
    {
        // Unique rule
        $duplicateRule = Rule::unique('users', 'alias');
        if ($userId) {
            logger()->debug('Adding {userId} to ignore list', compact('userId'));
            $duplicateRule = $duplicateRule->ignore($userId);
        }

        // Options
        $options = [
            'label' => 'Alias (optioneel)',
            'rules' => [
                'nullable',
                'string',
                'min:4',
                'regex:/^[a-z0-9][a-z0-9-_\.]{2,}[a-z0-9]$/i',
                $duplicateRule,
            ],
            'error_messages' => [
                'alias.min' => 'Je alias moet minimaal 4 tekens lang zijn',
                'alias.regex' => 'Je alias mag alleen bestaan uit letters, cijfers en eventueel streepjes in het midden',
                'alias.unique' => 'Deze alias is al in gebruik door een andere gebruiker.',
            ],
            'help_block' => [
                'text' => <<<'HTML'
                    Kies een optionele nickname die wordt getoond op de site in plaats van je voornaam<br />
                    Je kunt a-z, A-Z, 0-9 en eventueel leestekens in het midden gebruiken.
                    HTML,
            ],
            'attr' => [
                'pattern' => '[a-zA-Z0-9][a-zA-Z0-9-_\.]{2,}[a-zA-Z0-9]',
            ],
        ];

        // Return field
        return $this->add('alias', 'text', $this->dotMerge($options, $override));
    }

    /**
     * Adds names to the field.
     *
     * @return $this
     */
    protected function addNames(bool $disabled): self
    {
        if ($disabled) {
            return $this
                ->add('name', 'static', [
                    'label' => 'Naam',
                ])
                ->add('after_name', 'hidden');
        }

        return $this
            ->add('first_name', 'text', [
                'label' => 'Voornaam',
                'rules' => [
                    'required',
                    'string',
                ],
                'attr' => [
                    'autocomplete' => 'given-name',
                ],
            ])
            ->add('insert', 'text', [
                'label' => 'Tussenvoegsel',
                'rules' => [
                    'required',
                    'string',
                ],
                'attr' => [
                    'autocomplete' => 'additional-name',
                ],
            ])
            ->add('last_name', 'text', [
                'label' => 'Achternaam',
                'rules' => [
                    'required',
                    'string',
                ],
                'attr' => [
                    'autocomplete' => 'family-name',
                ],
            ])
            ->add('after_name', 'hidden');
    }

    /**
     * Builsd merged array from dot notation.
     */
    private function dotMerge(array $source, array $merges): array
    {
        // merge dot-notation
        foreach ($merges as $key => $value) {
            Arr::set($source, $key, $value);
        }

        return $source;
    }
}
