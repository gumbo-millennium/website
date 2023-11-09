<?php

declare(strict_types=1);

namespace App\Support\Traits;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

trait DownloadsImages
{
    /**
     * Downloads the given URL, and returns a local, public
     * path on success.
     */
    protected function downloadImage(string $url): ?string
    {
        $sinkFile = tempnam(sys_get_temp_dir(), 'migration-download');

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                return null;
            }

            return Storage::disk(Config::get('gumbo.images.disk'))->putStream(
                path_join(Config::get('gumbo.images.path'), 'shop/images/'),
                $response->toPsrResponse()->getBody(),
            );
        } catch (GuzzleException $exception) {
            return null;
        } finally {
            if (file_exists($sinkFile)) {
                @unlink($sinkFile);
            }
        }
    }
}
