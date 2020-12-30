<?php

declare(strict_types=1);

namespace App\Forms;

use App\Forms\Traits\UserDataForm;
use App\Models\User;
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
     *
     * @param User $user
     * @return void
     */
    public function setUser(User $user)
    {
        $this->userId = $user->id;
    }

    /**
     * Builds the form
     */
    public function buildForm()
    {
        $this
            ->addNames($this->formOptions['is-linked'] ?? false)
            ->addEmail($this->getUser(), [
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'help_block.text' => 'Je moet je e-mailadres na wijziging opnieuw verifiëren.',
            ])
            ->addAlias($this->getUser())
            ->add('submit', 'submit', [
                'label' => 'Opslaan',
            ]);
    }

    /**
     * Returns a proper user id
     *
     * @return int|null
     */
    protected function getUser(): ?int
    {
        return $this->userId ?? $this->formOptions['user-id'] ?? null;
    }
}
