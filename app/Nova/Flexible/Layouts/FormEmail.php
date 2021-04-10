<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use App\Models\FormLayout;

class FormEmail extends FormField
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'email';

    /**
     * The displayed title
     *
     * @var string
     */
    protected $title = 'Email address';

    /**
     * Converts a field to a formfield
     *
     * @return array
     */
    public function toFormField(): FormLayout
    {
        return FormLayout::merge(parent::toFormField(), null, 'email', [
            'rules' => [
                $this->getAttribute('required') ? 'required' : 'nullable',
                'email',
            ],
        ]);
    }
}
