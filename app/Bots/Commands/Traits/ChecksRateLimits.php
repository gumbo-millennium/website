<?php

declare(strict_types=1);

namespace App\Bots\Commands\Traits;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

trait ChecksRateLimits
{
    /**
     * Resets the rate limit for a user.
     */
    protected function forgetRateLimit(string $key): void
    {
        $fullKey = $this->getRateLimitKey($key);

        Log::debug('Reset rate limit {key} for {user-id} (as {full-key})', [
            'key' => $key,
            'full-key' => $fullKey,
            'user-id' => $this->update->message?->from?->id,
            'chat-id' => $this->update->message?->chat?->id,
        ]);

        RateLimiter::resetAttempts($fullKey);
    }

    /**
     * Check if the user has hit a rate limit. This is on a per-Telegram-user basis.
     * @param string $key Key to use to uniquely identify the rate limit
     * @param string $message Message to display
     * @param string $decay Decay time (in ISO 8601 format)
     * @return bool True if the user has hit the rate limit
     */
    protected function rateLimit(string $key, string $message = '⏸ Nog even wachten...', string $decay = 'PT5M'): bool
    {
        $fullKey = $this->getRateLimitKey($key);

        Log::debug('Checking ratelimit for {key} using {full-key} on {user-id}@{chat-id}', [
            'key' => $key,
            'full-key' => $fullKey,
            'user-id' => $this->update->message?->from?->id,
            'chat-id' => $this->update->message?->chat?->id,
        ]);

        // Use Laravel rate limiter
        if (RateLimiter::tooManyAttempts($fullKey, 1)) {
            $this->replyWithMessage([
                'text' => $message,
            ]);

            Log::debug('User {user-id} was bounced by the {full-key} ratelimiter', [
                'full-key' => $fullKey,
                'user-id' => $this->update->message?->from?->id,
                'chat-id' => $this->update->message?->chat?->id,
            ]);

            return true;
        }

        // Tap the rate limiter
        RateLimiter::hit($fullKey, Date::now()->add($decay)->diffInSeconds());

        Log::debug('User {user-id} was granted access past the {full-key} ratelimiter', [
            'full-key' => $fullKey,
            'user-id' => $this->update->message?->from?->id,
            'chat-id' => $this->update->message?->chat?->id,
        ]);

        return false;
    }

    /**
     * Returns the name of the key used to rate-limit.
     */
    private function getRateLimitKey(string $key): string
    {
        // Prefer the user ID, but fall back to the chat ID
        $user = $this->update->message?->from?->id ?? $this->update->message?->chat?->id ?? 'shared';

        // Combine all stuff to form the key
        return "tg:{$key}:{$user}";
    }
}
