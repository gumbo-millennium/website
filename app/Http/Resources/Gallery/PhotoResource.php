<?php

declare(strict_types=1);

namespace App\Http\Resources\Gallery;

use App\Fluent\Image;
use App\Models\Gallery\Photo;
use Generator;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Photo $resource
 */
class PhotoResource extends JsonResource
{
    private const IMAGE_SIZES = [
        'sm' => 576,
        'md' => 472,
        'lg' => 394,
        'full' => 1920,
    ];

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'created_at_label' => $this->resource->created_at?->isoFormat('D MMMM YYYY, HH:mm'),
            'taken_at_label' => $this->resource->taken_at?->isoFormat('D MMMM YYYY, HH:mm'),
            '_links' => [
                'album' => route('gallery.album', $this->resource->album),
                'like' => route('gallery.photo.react', $this->resource),
                'report' => route('gallery.photo.report', $this->resource),
                'thumbnails' => [...$this->toImages()],
            ],
        ]);
    }

    private function toImages(): Generator
    {
        foreach (self::IMAGE_SIZES as $key => $size) {
            yield [
                'size' => $key,
                'width' => $size,
                'url' => Image::make($this->resource->path)->width($size)->dpi(2)->webp()->getUrl(),
            ];
        }
    }
}
