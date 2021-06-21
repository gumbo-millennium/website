<?php

declare(strict_types=1);

namespace Laravel\Nova;

class Nova
{
    public static function __callStatic($method, $args)
    {
        // no-op
    }
}
