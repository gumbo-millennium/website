<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FormCheckbox extends Layout
{
    /**
     * The layout's unique identifier
     * @var string
     */
    protected $name = 'checkbox';

    /**
     * The displayed title
     * @var string
     */
    protected $title = 'Checkbox';

    /**
     * Get the fields displayed by the layout.
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Label', 'label')->rules('required'),
            Text::make('Helptekst', 'help')->nullable(),
            Boolean::make('Verplicht (moet worden aangevinkt)', 'required')
        ];
    }
}
