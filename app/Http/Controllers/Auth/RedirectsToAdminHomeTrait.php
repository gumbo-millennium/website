<?php

namespace App\Http\Controllers\Auth;

trait RedirectsToAdminHomeTrait
{
    /**
     * {@inheritdoc}
     */
    protected function redirectTo()
    {
        return route('admin.home');
    }
}
