<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            // Fallback to "my account"
            $fallback = \route('acount.index');

            // Change fallback to Nova if found and accessible
            if (Config::get('services.features.enable-nova') && Gate::allows('enter-admin')) {
                $fallback = Nova::path();
            }

            // Redirect to intended URL or fallback route
            return redirect()->intended($fallback);
        }

        return $next($request);
    }
}
