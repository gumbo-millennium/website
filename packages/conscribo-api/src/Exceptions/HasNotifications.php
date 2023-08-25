<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Exceptions;

use Throwable;

trait HasNotifications
{
    protected array $notifications = [];

    public function __construct(string $message, int $code = 0, Throwable $previous = null, array $notifications = [])
    {
        parent::__construct($message, $code, $previous);

        $this->notifications = $notifications ?? [$message];
    }

    public function getNotifications(): array
    {
        return $this->notifications;
    }
}
