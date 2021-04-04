<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;

class FormSelect extends FormField
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'select';

    /**
     * The displayed title
     *
     * @var string
     */
    protected $title = 'Options';

    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields()
    {
        return array_merge(parent::fields(), [
            Boolean::make('Meerkeuze', 'multiple'),
            KeyValue::make('Opties', 'options')
                ->keyLabel('Naam')
                ->valueLabel('Label')
                ->actionText('Optie toevoegen'),
        ]);
    }
}
