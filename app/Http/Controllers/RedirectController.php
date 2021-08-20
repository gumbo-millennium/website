<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Models\RedirectInstruction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

class RedirectController extends Controller
{
    public function index(): RedirectResponse
    {
        URL::forceRootUrl(Config::get('app.url'));

        return Response::redirectTo(URL::to('/'), RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    public function redirect(string $slug): RedirectResponse
    {
        URL::forceRootUrl(Config::get('app.url'));

        $slug = Str::start(trim($slug, '/'), '/');

        /** @var RedirectInstruction $redirectInstruction */
        $redirectInstruction = RedirectInstruction::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->first();

        if (! $redirectInstruction) {
            return Response::redirectTo(URL::to($slug), RedirectResponse::HTTP_FOUND);
        }

        abort_if($redirectInstruction->trashed(), RedirectResponse::HTTP_GONE);

        return Response::redirectTo(URL::to($redirectInstruction->path), RedirectResponse::HTTP_FOUND);
    }
}
