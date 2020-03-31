<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
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
        $response = $next($request);
        if (!$response instanceof ResponseInterface) {
            return $response;
        }

        return $response
            ->withHeader('Referer-Policy', 'strict-origin')
            ->withHeader('X-Content-Type-Policy', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-XSS-Protection', '1;mode=block');
    }
}
