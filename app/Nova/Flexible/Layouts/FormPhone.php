<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use App\Models\FormLayout;
use App\Rules\PhoneNumber;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
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
            Text::make(__('Phone country'), 'country')
                ->help(__('Two-letter country code (upper case)'))
                ->suggestions([
                    Str::upper(Lang::getLocale()),
                ])
                ->rules('required', 'regex:/^[A-Z]{2}$/'),
        ]);
    }

    /**
     * Converts a field to a formfield
     *
     * @return array
     */
    public function toFormField(): FormLayout
    {
        return FormLayout::merge(parent::toFormField(), null, 'text', [
            'type' => 'tel',
            'rules' => [
                $this->getAttribute('required') ? 'required' : 'nullable',
                new PhoneNumber($this->getAttribute('country')),
            ],
        ]);
    }
}
