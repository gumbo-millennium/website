<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Helpers\Str;
use App\Models\Activity;
use Illuminate\View\View;

class ActivityComposer
{
    public function compose(View $view): void
    {
        // No-op if the view does not have an activity
        if (! isset($view['activity'])) {
            return;
        }

        $activity = $view['activity'];

        $view->with([
            'ticketPrices' => self::getTicketDescription($activity),
            'visibilityIcon' => $activity->is_public ? 'solid/earth-europe' : 'solid/user-group',
            'visibilityTitle' => $activity->is_public ? 'Openbare activiteit' : 'Besloten activiteit',
        ]);
    }

    protected function getTicketDescription(Activity $activity): string
    {
        $ticketPrices = $activity->tickets
            ->pluck('total_price')
            ->sort()
            ->unique()
            ->map(fn ($price) => Str::price($price) ?? __('Free'));

        if ($ticketPrices->isEmpty()) {
            return 'Geen prijzen bekend';
        }

        if ($ticketPrices->count() == 1) {
            return $ticketPrices->first();
        }

        if ($ticketPrices->count() == 2) {
            return $ticketPrices->join(' of ');
        }

        return Str::lower(sprintf('van %s t/m %s', $ticketPrices->first(), $ticketPrices->last()));
    }
}
