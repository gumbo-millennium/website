<?php

declare(strict_types=1);

namespace Tests\Feature;

use Spatie\Flash\Flash;
use Spatie\Flash\Message;
use Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    /**
     * Checks if the Spatie Flash message contains the given $message.
     */
    protected function assertFlashMessageContains(string $message): void
    {
        $flashed = app(Flash::class)->getMessage();

        assert($flashed instanceof Message);
        $this->assertInstanceOf(Message::class, $flashed);
        $this->assertStringContainsString($message, $flashed->message);
    }
}
