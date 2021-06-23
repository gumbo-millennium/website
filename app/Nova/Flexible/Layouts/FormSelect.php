<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use App\Models\FormLayout;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;

class FormSelect extends FormField
{
    public const MAX_EXPANDED_COUNT = 5;

    /**
     * The layout's unique identifier.
     *
     * @var string
     */
    protected $name = 'select';

    /**
     * The displayed title.
     *
     * @var string
     */
    protected $title = 'Options';

    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields()
    {
        return array_merge(parent::fields(), [
            Boolean::make('Meerkeuze', 'multiple'),
            KeyValue::make('Opties', 'options')
                ->keyLabel('Naam')
                ->valueLabel('Label')
                ->actionText('Optie toevoegen'),
        ]);
    }

    /**
     * Converts a field to a formfield.
     *
     * @return array
     */
    public function toFormField(): FormLayout
    {
        $options = $this->getAttribute('options');
        $multiple = (bool) $this->getAttribute('multiple');
        $required = (bool) $this->getAttribute('required');

        if (! $required && ! $multiple) {
            $options = array_merge(
                ['' => '-'],
                $options,
            );
        }

        return FormLayout::merge(parent::toFormField(), null, $multiple ? 'choice' : 'select', [
            'choices' => $options,
            'multiple' => $multiple,
            'expanded' => true,
            'rules' => array_filter([
                $required ? 'required' : 'nullable',
                $multiple ? 'array' : null,
                Rule::in(array_keys($options)),
            ]),
        ]);
    }
}
