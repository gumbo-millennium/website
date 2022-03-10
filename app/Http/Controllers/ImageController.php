<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    /**
     * Renders the given image with the given glide parameters.
     * Always streams the response.
     */
    public function render(Request $request, string $path): StreamedResponse
    {
        $server = ServerFactory::create([
            'response' => new LaravelResponseFactory($request),

            // Source
            'source' => Storage::disk(Config::get('gumbo.glide.source-disk'))->getDriver(),
            'source_path_prefix' => Config::get('gumbo.glide.source-path') ?? '',

            // Cache
            'cache' => Storage::disk(Config::get('gumbo.glide.cache-disk'))->getDriver(),
            'cache_path_prefix' => Config::get('gumbo.glide.cache-path') ?? '.glide-cache',

            // URL
            'base_url' => 'img',
        ]);

        return $server->getImageResponse($path, $request->all());
    }
}
