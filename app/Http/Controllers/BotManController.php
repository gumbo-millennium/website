<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Providers\RouteServiceProvider;
use BotMan\BotMan\BotMan;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        // Register routes
        RouteServiceProvider::mapBotManCommands();

        // Now let BotMan handle it
        $botman = app('botman');
        \assert($botman instanceof BotMan);
        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('content.botman-tinker');
    }

    /**
     * Returns an image that was requested, from the resources/assets/images-internal/bots folder
     * @param string $asset
     * @return Response
     */
    public function image(Request $request, string $asset): Response
    {
        // Fail if invalid signature
        if (!$request->hasValidSignature()) {
            abort(404);
        }

        // Validate path
        if (!preg_match('/^([a-z0-9\-_\]\/)*[a-z0-9\-_\]\.([a-z]{1,5})$/i', $asset)) {
            abort(404);
        }

        // Get path and file handle
        $path = \resource_path("assets/images-internal/bots/{$asset}");
        $file = new File($path, false);

        // Fail if missing
        if (!\file_exists($path) || !$file->isFile()) {
            abort(404);
        }

        // Get clean filename
        $cleanName = Str::ascii($file->getBasename());

        // Return image
        return response()
            ->file($path, [
                'Content-Disposition' => "inline; filename=\"{$cleanName}\""
            ])
            ->setAutoEtag()
            ->setCache(['public', 'no-store', 'no-transform'])
            ->setExpires(now()->addHour());
    }
}
