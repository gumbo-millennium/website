<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Ensure the HTTP call accepts JSON responses.
 */
class MustAcceptJson
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response|RedirectResponse)  $next
     * @return RedirectResponse|Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Ensure the request accepts JSON
        abort_unless(
            $request->accepts('application/json'),
            Response::HTTP_NOT_ACCEPTABLE,
            'This resource exclusively generates application/json responses but you do not seem to accept it.',
        );

        // All good, continue
        return $next($request);
    }
}
