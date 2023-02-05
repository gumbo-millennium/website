<?php

declare(strict_types=1);

namespace App\Http\Resources\Gallery;

use App\Helpers\Arr;
use App\Models\Gallery\Album;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property-read Album $resource
 */
class AlbumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return Arr::except(parent::toArray($request), ['photos']) + [
            'images' => PhotoResource::collection($this->resource->photos),
        ];
    }
}
