<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Only allow members here.
 */
class MembersOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guest()) {
            return new UnauthorizedHttpException();
        }

        if (! Auth::user()->hasRole('member')) {
            throw new AccessDeniedHttpException('You have to be a member to view this resource.');
        }

        return $next($request);
    }
}
