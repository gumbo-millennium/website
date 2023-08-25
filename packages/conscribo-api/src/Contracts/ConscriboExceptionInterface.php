<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Contracts;

use Throwable;

interface ConscriboException
{
    public function __construct(string $message, int $code = 0, Throwable $previous = null, array $notifications = []);

    public static function determineCodeFromNotification(string $notification): ?int;

    public function getNotifications(): array;
}
