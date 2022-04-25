<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Gallery\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class GalleryService
{
    public function photoBefore(Photo $photo): ?Photo
    {
        return $this->photoQuery($photo, '<')
            ->latest('taken_at')
            ->first();
    }

    public function photoAfter(Photo $photo): ?Photo
    {
        return $this->photoQuery($photo, '>')
            ->oldest('taken_at')
            ->first();
    }

    private function photoQuery(Photo $photo, string $operator): Builder
    {
        return Photo::query()
            ->whereAlbumId($photo->album_id)
            ->when($photo->taken_at, fn ($query) => $query->where('taken_at', $operator, $photo->taken_at))
            ->when($photo->taken_at === null, fn ($query) => $query->where('id', $operator, $photo->id))
            ->where('id', '!=', $photo->id);
    }
}
