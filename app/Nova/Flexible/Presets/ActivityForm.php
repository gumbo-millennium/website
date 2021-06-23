<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Presets;

use App\Nova\Flexible\Layouts\FormCheckbox;
use App\Nova\Flexible\Layouts\FormContent;
use App\Nova\Flexible\Layouts\FormEmail;
use App\Nova\Flexible\Layouts\FormField;
use App\Nova\Flexible\Layouts\FormPhone;
use App\Nova\Flexible\Layouts\FormSelect;
use App\Nova\Flexible\Layouts\FormTextArea;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

/**
 * Layout that allows admins to create a form.
 */
class ActivityForm extends Preset
{
    public const LAYOUTS = [
        FormField::class,
        FormEmail::class,
        FormPhone::class,
        FormSelect::class,
        FormTextArea::class,
        FormCheckbox::class,
        FormContent::class,
    ];

    /**
     * Execute the preset configuration.
     *
     * @return void
     */
    public function handle(Flexible $field)
    {
        // Change button
        $field->button('Formulierveld toevoegen');

        // Add text field
        foreach (self::LAYOUTS as $layout) {
            $field->addLayout($layout);
        }
    }
}
