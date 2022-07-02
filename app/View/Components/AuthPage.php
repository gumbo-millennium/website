<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class AuthPage extends Page
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return View::make('components.auth-page');
    }
}
