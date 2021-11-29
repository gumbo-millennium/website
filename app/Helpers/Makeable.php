<?php

declare(strict_types=1);

namespace App\Helpers;

trait Makeable
{
    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }
}
