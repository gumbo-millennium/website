<?php

declare(strict_types=1);

namespace App\Forms;

use App\Forms\Traits\UserDataForm;
use App\Helpers\Arr;
use App\Helpers\Str;
use App\Models\User;
use Illuminate\Validation\Rule;
use Kris\LaravelFormBuilder\Form;

/**
 * Account info update form
 *
 * @package App\Forms
 */
class AccountEditForm extends Form
{
    use UserDataForm;

    private ?int $userId = null;

    /**
     * Overrides user
     * @param User $user
     * @return void
     */
    public function setUser(User $user)
    {
        $this->userId = $user->id;
    }

    /**
     * Returns a proper user id
     * @return null|int
     */
    protected function getUser(): ?int
    {
        return $this->userId ?? $this->formOptions['user-id'] ?? null;
    }

    /**
     * Builds the form
     */
    public function buildForm()
    {
        $this
            ->addEmail($this->getUser(), [
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'help_block.text' => 'Je moet je e-mailadres na wijziging opnieuw verifiëren.'
            ])
            ->addAlias($this->getUser())
            ->add('submit', 'submit', [
                'label' => 'Opslaan'
            ]);
    }
}
