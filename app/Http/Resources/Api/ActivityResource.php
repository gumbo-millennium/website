<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Activity as ActivityModel;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        /** @var ActivityModel $resource */
        $resource = $this->resource;

        return array_merge(parent::toArray($request), [
            'created_at' => $resource->created_at->format('Y-m-d'),
            'updated_at' => $resource->updated_at->format('Y-m-d'),
            'deleted_at' => $resource->deleted_at?->format('Y-m-d'),
            'description' => $resource->description_html,
            'organizer' => $resource->role?->name,
            'links' => [
                'self' => route('api.activities.show', $resource),
                'enrollments' => route('api.enrollments.index', ['activity' => $resource->slug]),
            ],
        ]);
    }
}
