<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Exceptions;

use LogicException;
use RuntimeException;

class GenericException extends RuntimeException implements ConscriboException
{
    public const CODE_UNKNOWN = 999;

    protected static function determineCodeFromNotification(string $notification): ?int
    {
        throw new LogicException('Generic Exception cannot compuite codes from a notification.');
    }
}
