<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use App\Facades\Markdown;
use App\Models\FormLayout;
use Laravel\Nova\Fields\Markdown as FieldsMarkdown;
use Laravel\Nova\Fields\Text;

class FormContent extends FormField
{
    /**
     * The layout's unique identifier.
     *
     * @var string
     */
    protected $name = 'content';

    /**
     * The displayed title.
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
            Text::make(__('Title'), 'title'),
            FieldsMarkdown::make(__('Content'), 'content')
                ->stacked()
                ->help(__('Full markdown support. HTML will be removed.')),
        ];
    }

    /**
     * Converts a field to a formfield.
     *
     * @return array
     */
    public function toFormField(): FormLayout
    {
        $markdown = Markdown::parseSafe($this->getAttribute('content'));

        return FormLayout::merge(parent::toFormField(), $this->getAttribute('title'), 'static', [
            'label' => null,
            'value' => $markdown,
        ]);
    }
}
