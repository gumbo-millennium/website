<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Only allow members here
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class MembersOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guest()) {
            return new UnauthorizedHttpException;
        }

        if (!Auth::user()->hasRole('member')) {
            throw new AccessDeniedHttpException('You have to be a member to view this resource.');
        }

        return $next($request);
    }
}
