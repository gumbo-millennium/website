<?php

declare(strict_types=1);

namespace App\Nova\Flexible\Layouts;

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
}
