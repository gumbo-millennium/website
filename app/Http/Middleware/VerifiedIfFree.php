<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class VerifiedIfFree
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get activity
        $activity = $request['activity'] ?? null;

        // Check user
        $user = $request->user();
        \assert($user instanceof MustVerifyEmail);

        // Skip if verified
        if ($user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Skip if non-free
        if (!$activity || !$activity->is_free) {
            return $next($request);
        }

        // Flash message
        \flash(
            "Je moet eerst je e-mailadres bevestigen, voordat je kan inschrijven voor {$activity->name}.",
            "warning"
        );

        // Redirect back to activity
        return \redirect()->route('activity.show', compact('activity'));
    }
}
