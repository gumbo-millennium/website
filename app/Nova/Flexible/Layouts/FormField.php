<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use App\Contracts\FormLayoutContract;
use App\Models\FormLayout;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FormField extends Layout implements FormLayoutContract
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
            Text::make('Helptekst', 'help')->nullable(),
            Boolean::make('Verplicht', 'required'),
        ];
    }

    /**
     * Converts a field to a formfield
     *
     * @return array
     */
    public function toFormField(): FormLayout
    {
        $config = [
            'label' => $this->getAttribute('label'),
        ];

        if ($help = $this->getAttribute('help')) {
            $config['help_block'] = [
                'text' => $help,
            ];
        }

        if ($this->getAttribute('required')) {
            $config['rules'] = [
                'required',
            ];
        }

        return new FormLayout($this->key(), 'text', $config);
    }
}
