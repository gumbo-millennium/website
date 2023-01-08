<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Enrollment;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class EnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        /** @var Enrollment $resource */
        $resource = $this->resource;

        return array_merge(parent::toArray($request), [
            'activity' => ActivityResource::make($resource->activity),
            'links' => [
                'self' => route('api.enrollments.show', $resource),
                'activity' => route('api.activities.show', $resource->activity->slug),
            ],
        ]);
    }
}
