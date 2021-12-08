<?php

declare(strict_types=1);

namespace App\Models\MediaLibrary;

use App\Helpers\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class LocalPathGenerator extends DefaultPathGenerator implements PathGenerator
{
    public const BASE_PATH = 'medialibrary/media';

    /**
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        return Str::finish(self::BASE_PATH, '/') . hash('sha256', "model.{$media->getKey()}");
    }
}
