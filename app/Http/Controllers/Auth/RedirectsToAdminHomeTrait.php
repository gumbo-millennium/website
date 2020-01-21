<?php

namespace App\Http\Controllers\Auth;

/**
 * Redirects users to the homepage.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait RedirectsToAdminHomeTrait
{
    /**
     * Redirect to homepage
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
