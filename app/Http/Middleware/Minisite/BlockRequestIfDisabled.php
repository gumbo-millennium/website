<?php

declare(strict_types=1);

namespace App\Http\Middleware\Minisite;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

/**
 * Block requests to a given domain if the site is disabled.
 */
class BlockRequestIfDisabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $sitename = parse_url($request->getUri(), PHP_URL_HOST);
        $siteconfig = Config::get('gumbo.minisites', [])[$sitename] ?? [];

        if (! Arr::get($siteconfig, 'enabled')) {
            return Response::view('minisite.disabled', [
                'sitename' => $sitename,
            ], HttpResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
