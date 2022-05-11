<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException as FilesystemFileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    /**
     * Renders the given image with the given glide parameters.
     * Always streams the response, if found.
     */
    public function render(Request $request, string $path): StreamedResponse|HttpResponse
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

        try {
            return $server->getImageResponse($path, $request->all());
        } catch (FileNotFoundException | FilesystemFileNotFoundException) {
            return Response::noContent(HttpResponse::HTTP_NOT_FOUND);
        }
    }
}
