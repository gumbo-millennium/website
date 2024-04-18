<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Activity as ActivityModel;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
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

        return Collection::make([
            'id' => $resource->id,
            'name' => $resource->name,
            'slug' => $resource->slug,
            'description' => $resource->description_html,
            'organizer' => $resource->role?->name,
            'created_at' => $resource->created_at->format('Y-m-d'),
            'updated_at' => $resource->updated_at->format('Y-m-d'),
            'deleted_at' => $resource->deleted_at?->format('Y-m-d'),
            'actions' => [
                'self' => route('activity.show', $resource),
                'enroll' => route('enroll.show', $resource),
            ],
            'links' => [
                'self' => route('api.activities.show', $resource),
                'enrollments' => route('api.enrollments.index', ['activity' => $resource->slug]),
            ],
        ]);
    }
}
