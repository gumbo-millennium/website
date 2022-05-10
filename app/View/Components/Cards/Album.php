<?php

declare(strict_types=1);

namespace App\View\Components\Cards;

use App\Helpers\Str;
use App\Models\Gallery\Album as AlbumModel;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class Album extends Component
{
    private AlbumModel $album;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(AlbumModel $album)
    {
        $this->album = $album;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $album = $this->album;

        /**
         * @if ($longalbum)
         * <time datetime="{{ $album->start_date->toIso8601String() }}">Vanaf {{ $album->start_date->isoFormat('DDD dd MMM, HH:ii') }}</time>
         * @else
         * <time datetime="{{ $album->start_date->toIso8601String() }}">Op {{ $album->start_date->isoFormat('DDD dd MMM') }} om {{ $album->start_date->isoFormat('HH:mm') }}</time>
         * @endif
         * @if ($album->available_seats < 9000)
         * <span>{{ $album->available_seats }} plaatsen</span>
         * @endif
         * <time datetime="2020-03-10"> Mar 10, 2020 </time>
         * <span aria-hidden="true"> &middot; </span>
         * <span> 4 min read </span>
         */
        $photoCount = $album->photos->count();

        $stats = [
            trans_choice(':count photo|:count photos', $album->photos->count()),
            trans('Last updated') . ' ' . $album->updated_at->isoFormat('D MMM YYYY'),
        ];

        $authorTitle = ($ownerName = $album->user?->public_name)
            ? "Album van {$ownerName}"
            : 'Album van onbekende auteur';

        return View::make('components.card', [
            'href' => route('gallery.album', $album),
            'image' => $album->cover_image,
            'title' => $album->name,
            'description' => Str::words($album->description, 10),
            'footerIcon' => 'solid/images',
            'footerTitle' => $authorTitle,
            'footerText' => implode(' • ', $stats),
        ]);
    }
}
