<?php

declare(strict_types=1);

namespace App\Jobs\Tenor;

use App\Services\TenorGifService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use RuntimeException;

class SearchGifJob extends TenorJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $group,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TenorGifService $gifService): void
    {
        // Check the group
        $groupConfig = $gifService->getTermConfig($this->group);
        if (! $groupConfig || ! Arr::has($groupConfig, ['term', 'limit'])) {
            throw new InvalidArgumentException("Invalid group {$this->group}");
        }

        // Check for sanity
        if (! is_string($groupConfig['term']) || ! is_int($groupConfig['limit']) || $groupConfig['limit'] < 1 || $groupConfig['limit'] > 20) {
            throw new InvalidArgumentException("Invalid group configuration for {$this->group}");
        }

        // Check the API key
        $apiKey = $gifService->getApiKey();
        if (empty($apiKey)) {
            throw new RuntimeException('No API key configured for Tenor.');
        }

        // Make the request
        $response = Http::get('https://tenor.googleapis.com/v2/search', [
            'key' => $apiKey,
            'q' => $groupConfig['term'],
            'client_key' => parse_url(URL::to('/'), PHP_URL_HOST),
            'country' => 'NL',
            'locale' => 'nl_NL',
            'contentfilter' => 'low',
            'media_filter' => 'mediumgif',
            'ar_range' => 'wide',
            'limit' => 50,
        ]);

        if (! $response->successful()) {
            $httpException = $response->toException();

            $httpError = $response->json('error.message') ?? $httpException->getMessage();

            throw new RuntimeException(
                "Failed to fetch info for {$this->group}: {$httpError}",
                $httpException->getCode(),
                $httpException,
            );
        }

        $results = $response->json('results', []);

        $remainingVideos = $groupConfig['limit'];

        // Create a new job to download each result
        foreach ($results as $result) {
            // Find the download URL
            $downloadUrl = Arr::get($result, 'media_formats.mediumgif.url');
            if (! $downloadUrl) {
                continue;
            }

            // Just run somewhere else.
            DownloadGifJob::dispatch(
                group: $this->group,
                fileId: $result['id'],
                fileUrl: $downloadUrl,
            );

            // Ensure we don't download too many gifs
            if (--$remainingVideos < 1) {
                break;
            }
        }
    }
}
