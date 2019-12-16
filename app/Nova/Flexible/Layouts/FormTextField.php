<?php

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FormTextField extends Layout
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'text-field';

    /**
     * The displayed title
     *
     * @var string
     */
    protected $title = 'Text Field';

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
            Boolean::make('Required', 'required')
        ];
    }

}
