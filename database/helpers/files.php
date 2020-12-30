<?php

declare(strict_types=1);

use App\Helpers\Str;
use Illuminate\Http\File;
use Illuminate\Support\Collection;

return static function ($path, $extension): Collection {
    $imageDir = resource_path($path);
    $images = scandir($imageDir);
    return $images === false ? collect() : collect($images)
        ->filter(static fn ($name) => Str::endsWith($name, ".{$extension}"))
        ->map(static fn ($file) => new File("{$imageDir}/{$file}"));
};
