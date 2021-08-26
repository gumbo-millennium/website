<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\FormLayout;

/**
 * Explains conversion from layouts to actual Form Builder forms.
 */
interface FormLayoutContract
{
    /**
     * Converts a field to a formfield.
     */
    public function toFormField(): FormLayout;
}
