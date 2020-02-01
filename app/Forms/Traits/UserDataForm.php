<?php

declare(strict_types=1);

namespace App\Forms\Traits;

use App\Helpers\Arr;
use App\Models\User;
use Illuminate\Validation\Rule;
use Kris\LaravelFormBuilder\Form;

/**
 * Registration form
 */
trait UserDataForm
{
    /**
     * Builsd merged array from dot notation
     *
     * @param array $source
     * @param array $merges
     * @return array
     */
    private function dotMerge(array $source, array $merges): array
    {
        // merge dot-notation
        foreach ($merges as $key => $value) {
            Arr::set($source, $key, $value);
        }
        return $source;
    }
    /**
     * Add email field
     *
     * @param int|null $user
     * @param array $override
     * @return Kris\LaravelFormBuilder\Form|UserDataForm
     */
    public function addEmail(int $userId = null, array $override = []): Form
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
                    'alias.unique' => 'Er bestaat al een account met dit e-mailadres.'
                ],
                'attr' => [
                    'autocomplete' => 'email'
                ],
            ];

        return $this->add('email', 'email', $this->dotMerge($options, $override));
    }

    /**
     * Add alias
     *
     * @param int|null $user
     * @param array $override
     * @return Kris\LaravelFormBuilder\Form|UserDataForm
     */
    public function addAlias(int $userId = null, array $override = []): Form
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
                'regex:/^[a-z0-9][a-z0-9\-_\.]{2,}[a-z0-9]$/',
                $duplicateRule,
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
                Kies een optionele nickname die wordt getoond op de site in plaats van je voornaam<br />
                Je kunt a-z, 0-9 en eventueel leestekens in het midden gebruiken.
                HTML,
                // phpcs:enable Generic.Files.LineLength.TooLong
            ],
            'attr' => [
                'autocomplete' => 'nickname',
                'pattern' => '[a-z0-9][a-z0-9-]{2,}[a-z0-9]'
            ],
        ];

        // Return field
        return $this->add('alias', 'text', $this->dotMerge($options, $override));
    }
}
