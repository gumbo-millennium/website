<?php

declare(strict_types=1);

namespace Database\Factories\Traits;

use App\Helpers\Arr;
use App\Helpers\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use InvalidArgumentException;

trait HasFileFinder
{
    protected static function findFilesInDir(string $path, string $extension): Collection
    {
        static $imageCache = [];
        static $fs = null;

        $fs ??= new Filesystem();
        $cacheKey = sprintf('%s:%s', rtrim($path, '/'), $extension);

        if (isset($imageCache[$cacheKey])) {
            return $imageCache[$cacheKey];
        }

        $actualPath = Arr::first([
            $path,
            resource_path($path),
            public_path($path),
        ], fn ($path) => $fs->isDirectory($path));

        throw_if($actualPath === null, InvalidArgumentException::class, "Directory not found: ${path}");

        $result = Collection::make();
        foreach ($fs->files($path) as $path) {
            if (! Str::lower($path->getExtension()) === $extension) {
                continue;
            }

            $result->push(new File($path->getPathname()));
        }

        return $imageCache[$cacheKey] = $result;
    }

    /**
     * @param string $path Path to scan for files
     * @return Collection<File> Files found
     * @throws InvalidArgumentException If the folder can't be found
     */
    protected function findImages(string $path): Collection
    {
        return $this->findFiles($path, 'jpg');
    }

    /**
     * @param string $path Path to scan for files
     * @return Collection<File> Files found
     * @throws InvalidArgumentException If the folder can't be found
     */
    protected function findFiles(string $path, string $extension): Collection
    {
        return self::findFilesInDir($path, $extension);
    }
}
