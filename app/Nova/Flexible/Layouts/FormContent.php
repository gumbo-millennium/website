<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Trix;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FormContent extends Layout
{
    /**
     * The layout's unique identifier
     * @var string
     */
    protected $name = 'content';

    /**
     * The displayed title
     * @var string
     */
    protected $title = 'Arbitrary content';

    /**
     * Get the fields displayed by the layout.
     * @return array
     */
    public function fields()
    {
        return [
            Trix::make('Content', 'content')->stacked()
        ];
    }
}
