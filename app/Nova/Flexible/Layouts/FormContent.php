<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use App\Models\FormLayout;
use Laravel\Nova\Fields\Trix;

class FormContent extends FormField
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'content';

    /**
     * The displayed title
     *
     * @var string
     */
    protected $title = 'Arbitrary content';

    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Trix::make('Content', 'content')->stacked(),
        ];
    }

    /**
     * Converts a field to a formfield
     *
     * @return array
     */
    public function toFormField(): FormLayout
    {
        return FormLayout::merge(parent::toFormField(), null, 'static', [
            'value' => $this->getAttribute('content'),
        ]);
    }
}
