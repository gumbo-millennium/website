<?php

declare(strict_types=1);

namespace App\Forms;

use App\Http\Controllers\Account\GrantsController;
use App\Models\User;
use Kris\LaravelFormBuilder\Form;

/**
 * Account info update form.
 *
 * @method User getModel()
 */
class AccountGrantsForm extends Form
{
    /**
     * Builds the form.
     */
    public function buildForm()
    {
        $user = $this->getModel();
        assert($user instanceof User);

        // Add all grants
        foreach (GrantsController::getGrants() as $grant) {
            $this->add($grant->key, 'checkbox', [
                'label' => $grant->name,
                'help_block' => [
                    'text' => $grant->description,
                ],
                'default_value' => $user->hasGrant($grant->key),
                'value_property' => "grants->{$grant->key}",
            ]);
        }

        // Add a submit button too
        $this
            ->add('submit', 'submit', [
                'label' => 'Opslaan',
            ]);
    }
}
