<?php

declare(strict_types=1);

namespace App\BotMan\Middleware;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Received;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class LogsReceives implements Received
{
    /**
     * Handle an incoming message.
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     * @return mixed
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function received(IncomingMessage $message, $next, BotMan $bot)
    {
        logger()->debug('[botman] Received message.', compact('message'));
        return $next($message);
    }
}
