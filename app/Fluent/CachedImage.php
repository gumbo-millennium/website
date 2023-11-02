<?php

declare(strict_types=1);

namespace App\Fluent;

use App\Services\ImageService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;

class CachedImage implements Htmlable, JsonSerializable, Stringable
{
    /**
     * Simple method to return the service, and to ensure the proper type is returned.
     */
    protected static function service(): ImageService
    {
        return app(ImageService::class);
    }

    public function __construct(
        private readonly Model $model,
        private readonly string $attributeValue,
    ) {
        //
    }

    /**
     * Returns the raw value of the attribute.
     */
    public function getValue(): string
    {
        return $this->attributeValue;
    }

    /**
     * Returns the path to the given image size.
     * @throws InvalidArgumentException
     */
    public function getPathTo(string $size): string
    {
        return self::service()->getStoragePathForImage($this->model, $this->attributeValue, $size);
    }

    /**
     * Returns the URL to the given image size.
     * @throws InvalidArgumentException
     */
    public function getUrlTo(string $size): string
    {
        return self::service()->getPublicUrlForImage($this->model, $this->attributeValue, $size);
    }

    public function getDefaultUrl(): string
    {
        return $this->getUrlTo(self::service()->getImageSizes()[0]);
    }

    public function toHtml()
    {
        return $this->getDefaultUrl();
    }

    public function jsonSerialize(): mixed
    {
        return $this->getDefaultUrl();
    }

    /**
     * Helper to simply use $model->image->full instead of requiring the user to call getUrlTo('full').
     * @param string $size the size to get the URL for
     * @return string the full URL to the given size
     * @throws InvalidArgumentException if in debug and the user tries to access an invalid size
     */
    public function __get(string $size): string
    {
        try {
            return $this->getUrlTo($size);
        } catch (InvalidArgumentException $e) {
            Log::error('Image size {size} does not exist for model {model}.', [
                'size' => $size,
                'model' => $this->model,
            ]);

            if (App::hasDebugModeEnabled()) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * Helper to simply use isset($model->image->full) instead of requiring the user to call isset($model->image->full).
     * @throws BindingResolutionException
     */
    public function __isset(string $key): bool
    {
        return array_key_exists($key, self::service()->getImageSizes())
            && ! empty($this->attributeValue);
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
