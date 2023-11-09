<?php

declare(strict_types=1);

namespace App\Bots\Commands\Traits;

use App\Events\Tenor\GifSharedEvent;
use App\Helpers\Str;
use App\Services\TenorGifService;
use Illuminate\Support\Facades\App;
use RuntimeException;
use Telegram\Bot\FileUpload\InputFile;

trait UsesPreloadedGifs
{
    /**
     * Sends a random, pre-cached reply gif.
     * @return null|InputFile|string URL to the selected GIF, or null if none were available
     */
    public function getReplyGifUrl(string $group): null|string|InputFile
    {
        // Search group is invalid
        if (Str::slug($group) !== $group) {
            return null;
        }

        /** @var TenorGifService */
        $service = app(TenorGifService::class);

        if (! $service->getApiKey()) {
            return null;
        }

        try {
            $gif = $service->getGifPathFromGroup($group);

            $result = App::isLocal()
                ? InputFile::createFromContents($service->getDisk()->get($gif), "{$group}.gif")
                : InputFile::create($service->getDisk()->url($gif), "{$group}.gif");

            GifSharedEvent::dispatch(basename($gif, '.gif'), $group);

            return $result;
        } catch (RuntimeException) {
            return null;
        }
    }
}
