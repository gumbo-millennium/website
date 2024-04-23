<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\Minisite;

use App\Http\Resources\Api\ActivityResource;
use App\Models\Minisite\Site;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * @property Site $resource
 */
class SiteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return Collection::make()
            ->merge($this->resource->only([
                'domain',
                'name',
                'enabled',
            ]))
            ->when(
                $this->resource->relationLoaded('activity'),
                fn ($col) => $col->put('activity', ActivityResource::make($this->resource->activity)),
            );
    }
}
