<?php

declare(strict_types=1);

namespace App\Listeners\Tenor;

use App\Events\Tenor\GifSharedEvent;
use App\Services\TenorGifService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;

/**
 * Notifies Tenor when a Gif is shared by a user.
 */
class NotifyTenorOnGifShareListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly TenorGifService $gifService)
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(GifSharedEvent $event)
    {
        $gifService = $this->gifService;

        // Make the request
        Http::get('https://tenor.googleapis.com/v2/registershare', [
            'key' => $gifService->getApiKey(),
            'client_key' => $gifService->getClientApiKey(),
            'id' => $event->getFileId(),
            'q' => $event->getSearchTerm(),

            'country' => 'NL',
            'locale' => 'nl_NL',
        ]);
    }
}
