<?php

declare(strict_types=1);

namespace App\View\Components\Cards;

use App\Helpers\Str;
use App\Models\Activity as ActivityModel;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class Activity extends Component
{
    private ActivityModel $activity;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(ActivityModel $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $activity = $this->activity;

        $longActivity = $activity->start_date->diffInHours($activity->end_date) > 6;

        $isFreeOrTicketless = $activity->tickets->count() === 0 || $activity->price_range === __('Free');

        if ($activity->is_public && $isFreeOrTicketless) {
            $activityLabel = __('Public activity');
        } elseif ($isFreeOrTicketless) {
            $activityLabel = __('Private activity');
        } else {
            $activityLabel = __('Paid activity');
        }

        /*
        @if ($longActivity)
          <time datetime="{{ $activity->start_date->toIso8601String() }}">Vanaf {{ $activity->start_date->isoFormat('DDD dd MMM, HH:ii') }}</time>
          @else
          <time datetime="{{ $activity->start_date->toIso8601String() }}">Op {{ $activity->start_date->isoFormat('DDD dd MMM') }} om {{ $activity->start_date->isoFormat('HH:mm') }}</time>
          @endif
@if ($activity->available_seats < 9000)
            <span>{{ $activity->available_seats }} plaatsen</span>
          @endif
          <time datetime="2020-03-10"> Mar 10, 2020 </time>
          <span aria-hidden="true"> &middot; </span>
          <span> 4 min read </span>
        */

        return View::make('components.card', [
            'href' => route('activity.show', $activity),
            'image' => $activity->poster,
            'title' => $activity->name,
            'lead' => $activityLabel,
            'description' => $activity->tagline ?? Str::words(strip_tags($activity->description_html ?? ''), 10),
        ]);
    }
}
