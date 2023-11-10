<?php

declare(strict_types=1);

namespace App\Bots\Commands\Traits;

trait IdentifiesChatTypes
{
    protected function isInGroupChat(): bool
    {
        $message = $this->update?->message;

        return $message && $message->chat->id === $message->from->id;
    }
}
