<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Traits;

/**
 * Redirects users to the homepage.
 */
trait RedirectsToHomepage
{
    /**
     * Redirect to homepage.
     */
    protected string $redirectTo = '/';

    /**
     * {@inheritdoc}
     */
    protected function redirectTo(): string
    {
        return route('home', null, false);
    }
}
