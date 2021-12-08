<?php

declare(strict_types=1);

namespace App\Models\MediaLibrary;

use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator;

class ProtectedFileUrlGenerator extends DefaultUrlGenerator implements UrlGenerator
{
    /**
     * Get the URL for the profile of a media item.
     *
     * @throws UrlCouldNotBeDeterminedException
     */
    public function getUrl(): string
    {
        return URL::route('files.download-single', ['media' => $this->media]);
    }
}
