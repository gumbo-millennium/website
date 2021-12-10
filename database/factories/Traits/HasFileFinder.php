<?php

declare(strict_types=1);

namespace Database\Factories\Traits;

use App\Helpers\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

trait HasFileFinder
{
    /**
     * @param string $path Path to scan for files
     * @return Collection<File> Files found
     * @throws DirectoryNotFoundException If the folder can't be found
     */
    protected function findImages(string $path): Collection
    {
        return $this->findFiles($path, 'jpg');
    }

    /**
     * @param string $path Path to scan for files
     * @return Collection<File> Files found
     * @throws DirectoryNotFoundException If the folder can't be found
     */
    protected function findFiles(string $path, string $extension): Collection
    {
        static $imageCache = [];
        static $fs = null;

        $fs ??= new Filesystem();
        $cacheKey = sprintf('%s:%s', rtrim($path, '/'), $extension);

        if (isset($imageCache[$cacheKey])) {
            return $imageCache[$cacheKey];
        }

        $result = Collection::make();
        foreach ($fs->files($path) as $path) {
            if (! Str::lower($path->getExtension()) === $extension) {
                continue;
            }

            $result->push(new File($path->getPathname()));
        }

        return $imageCache[$cacheKey] = $result;
    }
}
