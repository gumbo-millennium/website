<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Facades;

use Gumbo\ConscriboApi\Contracts\ConscriboApiClient;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @see \Gumbo\ConscriboApi\Contracts\ConscriboApiClient
 */
class Facade extends IlluminateFacade
{
    protected static function getFacadeAccessor()
    {
        return ConscriboApiClient::class;
    }
}
