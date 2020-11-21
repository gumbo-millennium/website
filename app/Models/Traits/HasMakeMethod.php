<?php

declare(strict_types=1);

namespace App\Models\Traits;

trait HasMakeMethod
{
    /**
     * Chainable factory method
     * @param mixed $args
     * @return self
     */
    public static function make(...$args): self
    {
        return new static(...$args);
    }
}
