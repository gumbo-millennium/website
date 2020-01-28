<?php

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FormSelect extends Layout
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
        return [
            Text::make('Label', 'label')->rules('required'),
            Text::make('Helptekst', 'help')->nullable(),
            Boolean::make('Verplicht', 'required'),
            Boolean::make('Meerkeuze', 'multiple'),
            KeyValue::make('Opties', 'options')
                ->rules('array', 'min:2')
                ->keyLabel('Naam')
                ->valueLabel('Label')
                ->actionText('Optie toevoegen'),
        ];
    }
}
