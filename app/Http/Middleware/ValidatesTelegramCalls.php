<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequiresSignedTelegramData
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        // Get hash
        $hash = $request->get('hash');

        // Get the data used to sign the request
        $baseArray = Collection::make($request->except('hash'))
        ->filter(static fn ($val, $key) => "$key=$val")
        ->sort()
            ->implode("\n");

        // Hash the bot token
        $botSecret = hash('sha256', BOT_TOKEN, true);

        // Get hash of the data
        $signed = hash_hmac('sha256', $baseArray, $botSecret);

        // Get hash
        if (!hash_equals($signed, $hash)) {
            throw new BadRequestHttpException('The data signature is invalid');
        }

        // Validate expiration
        $date = $request->get('auth_date');
        if (time() - $date > 60 * 60) {
            throw new BadRequestHttpException('The data signature is no longer valid');
        }

        // Request valid :)
        return $next($request);
    }
}
