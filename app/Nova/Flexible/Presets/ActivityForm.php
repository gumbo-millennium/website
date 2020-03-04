<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Presets;

use App\Nova\Flexible\Layouts\FormCheckbox;
use App\Nova\Flexible\Layouts\FormContent;
use App\Nova\Flexible\Layouts\FormEmail;
use App\Nova\Flexible\Layouts\FormPhone;
use App\Nova\Flexible\Layouts\FormSelect;
use App\Nova\Flexible\Layouts\FormTextArea;
use App\Nova\Flexible\Layouts\FormTextField;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

/**
 * Layout that allows admins to create a form.
 */
class ActivityForm extends Preset
{
    /**
     * Execute the preset configuration
     * @return void
     */
    public function handle(Flexible $field)
    {
        // Change button
        $field->button('Formulierveld toevoegen');

        // Add text field
        $field->addLayout(FormTextField::class);
        $field->addLayout(FormEmail::class);
        $field->addLayout(FormPhone::class);
        $field->addLayout(FormSelect::class);
        $field->addLayout(FormTextArea::class);
        $field->addLayout(FormCheckbox::class);
        $field->addLayout(FormContent::class);
    }
}
