<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

interface ShippableModel extends PayableModel
{
    /**
     * Returns the field to store when the object was shipped.
     */
    public function getShippedAtField(): string;
}
