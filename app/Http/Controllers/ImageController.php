<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\Request;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;

class ImageController extends Controller
{
    /**
     * Ensure only signed URLs can reach this route
     * @return void
     */
    public function __construct()
    {
        // Ensure all URLs are signed
        $this->middleware('signed');
    }

    /**
     * Converts a file with the given path using the given properties
     * @param Request $request
     * @param FilesystemManager $manager
     * @param mixed $path
     * @return mixed
     */
    public function show(Request $request, FilesystemManager $manager, $path)
    {
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
