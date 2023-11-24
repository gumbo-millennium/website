<?php

declare(strict_types=1);

namespace App\Exceptions\Services;

use RuntimeException;

class ConscriboException extends RuntimeException
{
    public const CODE_AUTH_MISSING = 11_001;

    public const CODE_AUTH_FAILED = 11_002;

    public const CODE_INVALID_REQUEST = 12_001;

    public const CODE_INVALID_RESPONSE = 12_002;

    public const CODE_API_UNAVAILABLE = 15_001;
}
