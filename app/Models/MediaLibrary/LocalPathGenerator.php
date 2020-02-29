<?php

declare(strict_types=1);

namespace App\Models\MediaLibrary;

use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

class LocalPathGenerator extends BasePathGenerator implements PathGenerator
{
    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        return sprintf('medialibrary/media/%s', hash('sha256', "model.{$media->getKey()}"));
    }
}
