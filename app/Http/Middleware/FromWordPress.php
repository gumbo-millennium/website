<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Services\WordPressAccessProvider;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Handles requests coming in from WordPress, by validating if it's JSON and if the content
 * signature is valid.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FromWordPress
{
    /**
     * Validate the content of an inbound request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, WordPressAccessProvider $wpProvider)
    {
        if (!$request->isJson()) {
            throw new BadRequestHttpException('Expected content to be JSON');
        }

        if (!$wpProvider->validateRequest($request)) {
            throw new AccessDeniedHttpException('Content signature invalid or missing');
        }

        $next($request);
    }
}
