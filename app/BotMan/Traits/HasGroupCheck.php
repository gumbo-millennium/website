<?php

declare(strict_types=1);

namespace App\BotMan\Traits;

use App\Helpers\Str;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Interfaces\DriverInterface;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\Drivers\Telegram\TelegramDriver;

trait HasGroupCheck
{
    /**
     * Returns if this is sent in a group
     * @return bool
     */
    protected function isInGroup(): bool
    {
        // Get bot driver and message
        $driver = $this->getBot()->getMessage();
        $message = $this->getBot()->getMessage();

        // Sanity check, and IDE hinting
        \assert($driver instanceof DriverInterface);
        \assert($message instanceof IncomingMessage);

        // Telegram check
        if ($driver instanceof TelegramDriver) {
            return $message->getSender() !== $message->getRecipient();
        }

        // Unknown
        return false;
    }

    /**
     * Returns true if the bot is mentioned in the chat
     * @return bool
     */
    protected function isMentioned(?string $username = null): bool
    {
        $bot = $this->getBot();
        \assert($bot instanceof BotMan);
        $message = $bot->getMessage();
        \assert($message instanceof IncomingMessage);

        // We can check for @ in HTTP drivers
        $driver = $bot->getDriver();
        if ($driver instanceof HttpDriver) {
            // Get username
            $username ??= $driver->getConfig()->get(
                'username',
                static fn () => Str::slug(config('app.name'))
            );

            // Find username in the text
            return Str::contains(
                Str::lower($message->getText()),
                Str::lower($username)
            );
        }

        // Don't know how to check other drivers yet
        return false;
    }
}
