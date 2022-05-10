<?php

declare(strict_types=1);

namespace App\View\Components\Cards;

use App\Helpers\Str;
use App\Models\NewsItem as NewsModel;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class News extends Component
{
    private NewsModel $item;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(NewsModel $item)
    {
        $this->item = $item;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $item = $this->item;

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
            'href' => route('news.show', $item),
            'image' => $item->cover,
            'title' => $item->title,
            'lead' => $item->category,
            'slot' => $item->headline ?? Str::words(strip_tags($item->html), 10),

            // Footer
            'footer-text' => "Gepubliceerd op {$item->published_at->isoFormat('ddd DD MMM YYYY')}",
        ]);
    }
}
