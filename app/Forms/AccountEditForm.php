<?php

declare(strict_types=1);

namespace App\Forms;

use App\Forms\Traits\UserDataForm;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

    private User $subject;

    /**
     * @param User User being modified, used for rules
     */
    public function __construct(User $subject)
    {
        $this->subject = $subject;
        $this->formOptions['model'] = $subject;
    }

    /**
     * Builds the form
     */
    public function buildForm()
    {
        $this
            ->addEmail($this->subject)
            ->addAlias($this->subject, [
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'help_block.text' => 'Je moet je e-mailadres na wijziging opnieuw verifiëren.'
            ])
            ->add('submit', 'submit', [
                'label' => 'Opslaan'
            ]);
    }
}
