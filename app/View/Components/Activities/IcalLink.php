<?php

declare(strict_types=1);

namespace App\View\Components\Activities;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class IcalLink extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $user = Auth::user();
        if (! $user) {
            return '';
        }

        $calendarUrl = URL::signedRoute('api.calendar.show', $user);
        $googleUrl = sprintf('https://calendar.google.com/calendar/render?cid=%s', urlencode($calendarUrl));

        return View::make('components.activities.ical-link', [
            'calendarUrl' => $calendarUrl,
            'googleUrl' => $googleUrl,
        ]);
    }
}
