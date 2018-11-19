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
     * {@inheritdoc}
     */
    protected function redirectTo()
    {
        return route('home', null, false);
    }
}
