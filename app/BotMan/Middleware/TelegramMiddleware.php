<?php

declare(strict_types=1);

namespace App\BotMan\Middleware;

use App\Helpers\Arr;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Received;
use BotMan\BotMan\Interfaces\Middleware\Sending;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\Drivers\Telegram\TelegramDriver;

/**
 * Add a helper to send messages as replies in Telegram.
 */
class TelegramMiddleware implements Sending, Received
{
    /**
     * Handle an outgoing message payload before/after it
     * hits the message service.
     * @param mixed $payload
     * @param callable $next
     * @param BotMan $bot
     * @return mixed
     */
    public function sending($payload, $next, BotMan $bot)
    {
        // Allow auto-reply
        $autoReply = isset($payload['auto-reply']);
        if ($autoReply) {
            unset($payload['auto-reply']);
        }

        // Ignore if not test
        if (empty($payload['text'])) {
            return $next($payload);
        }

        // Apply Telegram
        if ($bot->getDriver() instanceof TelegramDriver) {
            $payload = $this->applyTelegramSending($payload, $bot, $autoReply);
        }

        // Forward call
        return $next($payload);
    }

    /**
     * Handle an incoming message.
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     * @return mixed
     */
    public function received(IncomingMessage $message, $next, BotMan $bot)
    {
        // Apply Telegram
        if ($bot->getDriver() instanceof TelegramDriver) {
            $message = $this->applyTelegramReceived($message);
        }

        return $next($message);
    }

    /**
     * Applies stuff for Telegram
     * @param mixed $payload
     * @param BotMan $bot
     * @param bool $autoReply
     * @return array<string>
     */
    private function applyTelegramSending(array $payload, BotMan $bot, bool $autoReply)
    {
        // Enable Markdown
        $payload['parse_mode'] = 'HTML';

        // Check for groups
        $message = $bot->getMessage();
        $isGroup = $message->getSender() !== $message->getRecipient();

        // The reply is only required for groups
        if (!$isGroup) {
            return $payload;
        }

        // Make it a reply to the previous message if posted in a group
        $payload['reply_to_message_id'] = Arr::get($message->getPayload(), 'message_id');

        // Add keyboard in group mode
        if ($autoReply) {
            $payload['reply_markup'] = \json_encode([
                'force_reply' => true,
                'selective' => true
            ]);
        }

        return $payload;
    }

    private function applyTelegramReceived(IncomingMessage $message): IncomingMessage
    {
        // Check if we're in a group and if the message ends with a mention.
        if (
            $message->getSender() !== $message->getRecipient() &&
            \preg_match('/^(.+)\@(?:[a-z0-9_-]+)$/', $message->getText(), $matches)
        ) {
            $message->setText($matches[1]);
        }

        // Return message
        return $message;
    }
}
