<?php

declare(strict_types=1);

namespace App\Exceptions\Services;

use RuntimeException;

class GoogleServiceException extends RuntimeException
{
    public const CODE_DOMAIN_LOCKED = 1_001;

    public const CODE_GROUP_FAILED = 8_001;

    public const CODE_GROUP_PERMISSIONS_FAILED = 8_002;

    public const CODE_GROUP_ALIAS_FAILED = 8_003;

    public const CODE_GROUP_MEMBER_FAILED = 8_004;
}
