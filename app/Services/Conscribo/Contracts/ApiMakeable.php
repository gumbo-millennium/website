<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Contracts;

use Illuminate\Support\Collection;

interface ApiMakeable
{
    /**
     * Makes this collection using API mutations.
     * @return ApiMakeable
     */
    public static function apiMake(array $data): static;
}
