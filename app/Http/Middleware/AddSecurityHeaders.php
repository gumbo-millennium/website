<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Support\Responsable;
use Psr\Http\Message\ResponseInterface;

class AddSecurityHeaders
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Forward first
        $response = $next($request);

        // Check if the request is responsable
        if ($response instanceof Responsable) {
            $response = $response->toResponse($request);
        }

        // Check if the response is one we can add headers to
        if (!$response instanceof ResponseInterface) {
            return $response;
        }

        // Add security headers
        return $response
            ->withHeader('Referer-Policy', 'strict-origin')
            ->withHeader('X-Content-Type-Policy', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-XSS-Protection', '1;mode=block');
    }
}
