<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\Minisite;

use App\Models\Minisite\SitePage;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property SitePage $resource
 */
class SitePageSitemapResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return $this->resource->only([
            'id',
            'url',
            'updated_at',
        ]);
    }
}
