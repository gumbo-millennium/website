<?php

declare(strict_types=1);

namespace App\BotMan\Traits;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;

trait HasGroupCheck
{
    /**
     * Returns if this is sent in a group
     * @return bool
     */
    protected function isInGroup(): bool
    {
        $message = $this->getBot()->getMessage();
        assert($message instanceof IncomingMessage);

        return $message->getSender() !== $message->getRecipient();
    }
}
