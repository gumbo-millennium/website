<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use LogicException;
use Str;
use Throwable;

class ImageService
{
    protected readonly string $sourceDisk;

    protected readonly ?string $sourcePath;

    protected readonly string $storageDisk;

    protected readonly string $storagePath;

    protected readonly bool $useTemporaryUrls;

    protected readonly Collection $imageSizes;

    public function __construct(ConfigRepository $config)
    {
        // Load source settings
        $this->sourceDisk = (string) $config->get('images.source.disk');
        $this->sourcePath = $config->get('images.source.path');

        // Load storage settings
        $this->storageDisk = (string) $config->get('images.storage.disk');
        $this->storagePath = (string) $config->get('images.storage.path');
        $this->useTemporaryUrls = (bool) $config->get('images.storage.use_temporary_urls');

        // Load sizes
        $this->imageSizes = Collection::make($config->get('images.sizes', []))
            ->each(function ($row, $name) {
                throw_unless(is_string($name) && ! empty($name), InvalidArgumentException::class, 'Size name is invalid, should be a non-empty string.');
                throw_unless(is_array($row), InvalidArgumentException::class, "Size [{$name}] is invalid, should be an array.");
            })
            ->map(fn (array $value): array => [
                'height' => $value['height'] ?? null,
                'width' => $value['width'] ?? null,
                'crop' => $value['crop'] ?? false,
            ])
            ->sortBy(fn (array $value) => $value['width'] ?? $value['height'] ?? 0);
    }

    /**
     * Returns the name of the disk to get the source images from.
     */
    public function getSourceDiskName(): string
    {
        return $this->sourceDisk;
    }

    /**
     * Returns whether the given path is valid for the source disk.
     */
    public function isValidSourcePath(string $path): bool
    {
        return empty($this->sourcePath) || Str::startsWith($path, $this->sourcePath);
    }

    /**
     * Returns the name of the disk to use for the resized images.
     */
    public function getStorageDiskName(): string
    {
        return $this->storageDisk;
    }

    /**
     * Returns all available image sizes.
     * @return string[]
     */
    public function getImageSizes(): array
    {
        return $this->imageSizes->keys()->all();
    }

    /**
     * Returns the information available for the given size.
     * @throws InvalidArgumentException if the size does not exist
     */
    public function getImageSize(string $size): array
    {
        if (! $this->imageSizes->has($size)) {
            throw new InvalidArgumentException("Image size [{$size}] does not exist.");
        }

        return $this->imageSizes->get($size);
    }

    /**
     * Returns the base path for the given image.
     */
    public function getStorageBasePath(): string
    {
        return (string) Str::of($this->storagePath)->finish('/')->append('generated')->ltrim('/');
    }

    /**
     * Returns the storage path to the model, optionally even for the given model instance.
     * @throws InvalidCastException
     * @throws LazyLoadingViolationException
     * @throws LogicException
     */
    public function getStoragePathForModel(string|Model $model): string
    {
        $modelClass = (string) Str::of(class_basename($model::class))->plural()->snake();

        return implode('/', array_filter([
            $this->getStorageBasePath(),
            $modelClass,
            $model instanceof Model ? $model->getKey() : null,
        ]));
    }

    /**
     * Returns the storage path for the given attribute value on the given model.
     * @throws InvalidArgumentException if model is not persisted or attribute value is empty
     */
    public function getStoragePathForModelAttribute(Model $model, string $attributeValue): string
    {
        throw_unless($model->exists, InvalidArgumentException::class, 'Model must exist.');
        throw_unless(! empty($attributeValue), InvalidArgumentException::class, 'Attribute value should be a non-empty string.');

        return implode('/', [
            $this->getStoragePathForModel($model),
            substr(sha1($attributeValue), 0, 8),
        ]);
    }

    /**
     * Returns the storage path for the given image in the given size.
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public function getStoragePathForImage(Model $model, string $attributeValue, string $size): string
    {
        throw_unless($this->imageSizes->has($size), InvalidArgumentException::class, "Image size [{$size}] does not exist.");

        return implode('/', [
            $this->getStoragePathForModelAttribute($model, $attributeValue),
            "{$size}.webp",
        ]);
    }

    /**
     * Returns the URL to the given image in the given size. If the URLs are temporary, they will expire after 15 minutes.
     * @throws InvalidArgumentException if model is not persisted or attribute value is empty
     */
    public function getPublicUrlForImage(Model $model, string $attributeValue, string $size): string
    {
        $path = $this->getStoragePathForImage($model, $attributeValue, $size);

        if ($this->useTemporaryUrls) {
            return Storage::disk($this->storageDisk)->temporaryUrl($path, Date::now()->addMinutes(15));
        }

        return Storage::disk($this->storageDisk)->url($path);
    }
}
