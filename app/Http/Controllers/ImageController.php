<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Middleware\CheckSignedUrl;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as ActiveRoute;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;
use Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageController extends Controller
{
    /**
     * Ensure only signed URLs can reach this route
     * @return void
     */
    public function __construct()
    {
        // Ensure all URLs are signed
        $this->middleware(CheckSignedUrl::class);
    }

    /**
     * Converts a file with the given path using the given properties
     * @param Request $request
     * @param FilesystemManager $manager
     * @param mixed $path
     * @return mixed
     */
    public function show(Request $request, FilesystemManager $manager, ActiveRoute $route)
    {
        // Get path
        $path = $route->parameter('path');

        // Validate path
        if (!preg_match('/^[a-z0-9]+$/i', $path)) {
            throw new NotFoundHttpException();
        }

        // Decode path
        $safepath = $path . \str_repeat('=', \strlen($path) % 4);
        $path = \base64_decode($safepath);
        $pathAscii = Str::ascii($path);

        // Possible path pollution, throw 404
        if ($path !== $pathAscii) {
            dd([
                'path' => $path,
                'ascii' => $pathAscii,
                'safepath' => $safepath
            ]);
            throw new NotFoundHttpException();
        }

        // Require all links to be signed
        // Get disks
        $publicDisk = $manager->disk('public');
        $cacheDisk = $manager->disk();

        // Sanity checks
        \assert($publicDisk instanceof FilesystemAdapter);
        \assert($cacheDisk instanceof FilesystemAdapter);

        // Get factory
        $server = ServerFactory::create([
            'response' => new LaravelResponseFactory($request),
            'source' => $publicDisk->getDriver(),
            'cache' => $cacheDisk->getDriver(),
            'cache_path_prefix' => config('services.glide.cache-path', '/.image-cache/glide'),
            'base_url' => 'img',
            'presets' => config('services.glide.presets', [])
        ]);

        // Return image
        return $server->getImageResponse($path, $request->all());
    }
}
