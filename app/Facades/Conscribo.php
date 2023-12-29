<?php

declare(strict_types=1);

namespace App\Facades;

use App\Services\Conscribo\Contracts\Client as ClientContract;
use Illuminate\Support\Facades\Facade;

class Conscribo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ClientContract::class;
    }
}
