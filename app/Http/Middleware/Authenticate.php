<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;


class Authenticate extends Middleware
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * @return string
     */
    protected function redirectTo($request)
    {
        return route('login');
    }
}
