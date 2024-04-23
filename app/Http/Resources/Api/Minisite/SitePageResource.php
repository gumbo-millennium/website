<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\Minisite;

use App\Fluent\Image;
use App\Models\Minisite\SitePage;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * @property SitePage $resource
 */
class SitePageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $coverImage = Image::make($this->resource->cover);
        $resourceCovers = Collection::make(config('gumbo.image-presets'))
            ->map(fn ($_, string $key) => $coverImage->preset($key)->getUrl());

        return Collection::make()
            ->merge($this->resource->only([
                'id',
                'title',
                'url',
                'slug',
                'hidden',
                'contents',
                'created_at',
                'updated_at',
            ]))
            ->put('cover', $resourceCovers);
    }
}
