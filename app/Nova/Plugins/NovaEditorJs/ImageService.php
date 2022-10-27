<?php

namespace App\Nova\Plugins\NovaEditorJs;

use Advoor\NovaEditorJs\Services\DefaultImageUploadHandler;
use App\Fluent\Image;
use Illuminate\Support\Facades\Config;

class ImageService extends DefaultImageUploadHandler
{
    /**
     * Use custom image handler to build image URL if the image
     * is stored on the image disk.
     * @param string $path
     * @return string
     */
    protected function determineImageUrl(string $path): string
    {
        $imageDisk = $this->getImageDisk();

        if ($imageDisk === Config::get('gumbo.images.disk')) {
            return (string) Image::make($path)->width(1220)->height(1220)->webp();
        }

        return parent::determineImageUrl($path);
    }
}
