<?php

declare(strict_types=1);

namespace App\BotMan\Traits;

use BotMan\BotMan\BotMan;
use BotMan\Drivers\Telegram\TelegramDriver;

trait FormatsCommands
{
    /**
     * Formats the given command for the proper scope
     * @param BotMan $bot
     * @param string $command
     * @return null|string
     */
    protected function formatCommand(BotMan $bot, string $command): ?string
    {
        // Skip if wrong driver
        if (!$bot->getDriver() instanceof TelegramDriver) {
            return $command;
        }

        // Skip if non-group
        $message = $bot->getMessage();
        if ($message->getSender() === $message->getRecipient()) {
            return $command;
        }

        // Skip if no username
        $username = config('botman.telegram.username');
        if (!$username) {
            return $command;
        }

        return "{$command}@{$username}";
    }
}
