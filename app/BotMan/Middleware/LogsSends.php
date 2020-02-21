<?php

declare(strict_types=1);

namespace App\BotMan\Middleware;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Sending;

class LogsSends implements Sending
{
    /**
     * Handle an incoming message.
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     * @return mixed
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function sending($payload, $next, BotMan $bot)
    {
        logger()->debug('[botman] Sending message.', compact('payload'));
        return $next($payload);
    }
}
