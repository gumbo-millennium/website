<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Http\Request;

class StartCartSession
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        $user = $request instanceof Request ? $request->user() : null;
        if ($user) {
            Cart::session($user->id);
        }

        return $next($request);
    }
}
