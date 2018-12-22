<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Handles stateless authentication
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class AuthenticateOnceWithAuthBasic
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
        $auth = Auth::onceBasic();

        return ($auth !== null) ? $auth : $next($request);
    }
}
