<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;

class NoCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (! $response instanceof ResponseInterface) {
            return $response;
        }

        // Just the minimal headers, will only be a problem on IE but /care
        return $response
            ->withHeader('Cache-Control', 'no-cache, max-age=0');
    }
}
