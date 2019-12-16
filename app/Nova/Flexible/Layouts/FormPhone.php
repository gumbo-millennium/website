<?php

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FormPhone extends Layout
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'phone';

    /**
     * The displayed title
     *
     * @var string
     */
    protected $title = 'Phone number';

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
            Text::make('Default country', 'country')
                ->help('ISO 3166-1 alpha-2 country code (example: NL)')
                ->rules('required', 'regex:^[A-Z]{2}$'),
            Boolean::make('Required', 'required')
        ];
    }

}
