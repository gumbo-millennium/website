<?php

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FormCheckbox extends Layout
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'checkbox';

    /**
     * The displayed title
     *
     * @var string
     */
    protected $title = 'Checkbox';

    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Label', 'label')->rules('required'),
            Text::make('Help text', 'help')->nullable(),
            Boolean::make('Must be checked', 'required')
        ];
    }
}
