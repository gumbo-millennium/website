<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use App\Rules\PhoneNumber;
use Laravel\Nova\Fields\Text;

class FormPhone extends FormField
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
        return array_merge(parent::fields(), [
            Text::make('Standaard land', 'country')
                ->help('ISO 3166-1 alpha-2 landcode (voorbeeld: NL)')
                ->rules('required', 'regex:/^[A-Z]{2}$/'),
        ]);
    }
}
