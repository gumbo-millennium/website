<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Route;

class CheckSignedUrl
{
    /**
     * Computes the hash of the given URL
     * @param string $route
     * @param array $args
     * @return string
     */
    private static function getHash(string $route, array $args)
    {
        // Sort the args
        $mergedArgs = array_merge($args, ['signature' => 'xxxx']);
        ksort($mergedArgs);

        // Build a URL without a signature
        $original = URL::route($route, $mergedArgs);

        // Create a signature, but keep it short
        return substr(hash_hmac('sha256', $original, Config::get('app.key')), 0, 32);
    }

    /**
     * Creates a URL with a signature
     * @param string $route
     * @param array $args
     * @return string
     */
    public static function signUrl(string $route, array $args)
    {
        $hash = self::getHash($route, $args);
        return URL::route($route, $args + ['signature' => $hash]);
    }

    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $args = array_merge($request->all(), Route::getCurrentRoute()->parameters);
        $hash = self::getHash(Route::currentRouteName(), $args);

        if (isset($args['signature']) && \hash_equals($hash, $args['signature'])) {
            return $next($request);
        }

        throw new InvalidSignatureException();
    }
}
