<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Models\User;
use Telegram\Bot\Commands\Command as TelegramCommand;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User as TelegramUser;

abstract class Command extends TelegramCommand
{
    /**
     * Returns Telegram User
     * @return null|TelegramUser
     */
    protected function getTelegramUser(): ?TelegramUser
    {
        // Look for a message
        $message = $this->update->getMessage();
        if (!$message || !$message instanceof Message) {
            return null;
        }

        // Look for a user
        $chatUser = $message->from;
        if (!$chatUser || !$chatUser instanceof TelegramUser) {
            return null;
        }

        // Return user
        return $chatUser;
    }

    /**
     * Get the user based on the update
     * @return null|User
     */
    protected function getUser(): ?User
    {
        $chatUser = $this->getTelegramUser();

        // Skip if empty
        if (!$chatUser) {
            return null;
        }

        // Find the user that has this telegram ID
        return User::query()
            ->whereTelegramId((string) $chatUser->id)
            ->first();
    }

    /**
     * Runs a string through sprintf, and unwraps single newlines
     * @param string $text
     * @param mixed $arg
     * @return string
     */
    public function formatText(string $text, ...$args): string
    {
        $out = sprintf($text, ...$args);
        return preg_replace('/(?<!\n)\n(?=\S)/', ' ', $out);
    }
}
