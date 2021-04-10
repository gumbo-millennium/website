<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use App\Models\FormLayout;

class FormCheckbox extends FormField
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
     * Converts a field to a formfield
     *
     * @return array
     */
    public function toFormField(): FormLayout
    {
        return FormLayout::merge(parent::toFormField(), null, 'checkbox');
    }
}
