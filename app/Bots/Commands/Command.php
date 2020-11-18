<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Models\BotUserLink;
use App\Models\User;
use Telegram\Bot\Commands\Command as TelegramCommand;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User as TelegramUser;

abstract class Command extends TelegramCommand
{
    /**
     * Inherited update
     * @var null|Update
     */
    protected ?Update $update;

    /**
     * True if checked before, only checked once per request
     * @var bool
     */
    private bool $lookedForUser = false;

    /**
     * Found user, if any
     * @var null|User
     */
    private ?User $foundUser;

    /**
     * Get the user based on the update
     * @return null|User
     */
    protected function getUser(): ?User
    {
        // Check for a user
        if ($this->lookedForUser) {
            return $this->foundUser;
        }

        // Set defaults
        $this->lookedForUser = true;
        $this->foundUser = null;

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

        // Find a link between the Telegram user and the user list
        $link = User::query()
            ->whereTelegramId((string) $chatUser->id)
            ->first();

        // Return result from link
        if ($link) {
            return $this->foundUser = $link->user;
        }

        // Create link and return null
        $link = BotUserLink::createForDriver('telegram', "{$chatUser->id}", BotUserLink::TYPE_USER);
        $link->name = trim("{$chatUser->firstName} {$chatUser->lastName}") ?: null;
        $link->save();

        // Return null through
        return null;
    }
}
