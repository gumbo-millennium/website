<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property-read \App\Models\User $resource
 */
class UserResource extends JsonResource
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
            'name',
            'first_name',
            'insert',
            'last_name',
            'alias',
        ]);
    }
}
