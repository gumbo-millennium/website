<?php

declare(strict_types=1);

namespace App\View\Components\Layout;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class VerifyBanner extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        // Return empty if no user (null) or user is verified (true)
        if (Auth::user()?->hasVerifiedEmail() !== false) {
            return '';
        }

        return View::make('components.layout.verify-banner');
    }
}
