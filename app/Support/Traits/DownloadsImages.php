<?php

declare(strict_types=1);

namespace App\Support\Traits;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

trait DownloadsImages
{
    /**
     * Downloads the given URL, and returns a local, public
     * path on success.
     * @return string
     */
    protected function downloadImage(string $url): ?string
    {
        $sinkFile = tempnam(sys_get_temp_dir(), 'migration-download');

        try {
            $response = App::make(GuzzleClient::class)->get($url, [
                RequestOptions::TIMEOUT => 5,
                RequestOptions::SINK => $sinkFile,
                RequestOptions::ALLOW_REDIRECTS => [
                    'max' => 3,
                    'referer' => true,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            return Storage::disk(Config::get('gumbo.images.disk'))->putFile(
                path_join(Config::get('gumbo.images.path'), 'shop/images/'),
                new File($sinkFile)
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
