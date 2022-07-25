<?php

declare(strict_types=1);

namespace Tests\Traits;

use Spatie\Flash\Flash;
use Spatie\Flash\Message;

trait TestsFlashMessages
{
    /**
     * Checks if the Spatie Flash message contains the given $message.
     */
    protected function assertFlashMessageContains(string $message): void
    {
        /** @var Message $flashed */
        $flashed = app(Flash::class)->getMessage();

        $this->assertInstanceOf(Message::class, $flashed);
        $this->assertStringContainsString($message, $flashed->message);
    }
}
