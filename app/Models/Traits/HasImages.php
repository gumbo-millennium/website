<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Events\Images\ImageCreated;
use App\Events\Images\ImageDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasImages
{
    /**
     * The keys of the model's casts that are images.
     * @var null|string[]
     */
    private array|null $imageProperties = null;

    private array $originalImages = [];

    /**
     * Attach Eloquent events to update resources.
     * @return void
     */
    public static function bootHasImages()
    {
        static::saved(fn (Model $model) => $model->triggerImageUpdates());

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::forceDeleted(fn (Model $model) => $model->triggerImageUpdates());
        } else {
            static::deleted(fn (Model $model) => $model->triggerImageUpdates());
        }
    }

    /**
     * Returns properties that cast to an instance of ImageCast.
     * @return string[]
     */
    public function getImageProperties()
    {
        return $this->imageProperties ??= Collection::make($this->getCasts())
            ->filter(fn ($value) => is_a($value, ImageCast::class, true))
            ->keys()
            ->all();
    }

    public function triggerImageUpdates(): void
    {
        if (! $this->exists) {
            foreach ($this->getImageProperties() as $property) {
                $value = $this->getAttribute($property)?->getValue();
                ImageDeleted::dispatchIf($value, $this, $property, $value);
            }

            return;
        }

        foreach ($this->getImageProperties() as $property) {
            $value = $this->getAttribute($property)?->getValue();
            $originalValue = $this->getOriginal($property);

            ImageDeleted::dispatchIf($originalValue, $this, $property, $originalValue);
            ImageCreated::dispatchIf($value !== $originalValue, $this, $property, $value);
        }
    }

    /**
     * Triggers an image deletion event for all files associated with this image.
     * @internal
     */
    public function deleteImages(): void
    {
        $keysWithImages = Collection::make($model->getCasts())
            ->filter(fn ($value) => $value == ImageCast::class)
            ->keys()
            ->all();
    }
}
