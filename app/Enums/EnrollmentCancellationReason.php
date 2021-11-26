<?php

declare(strict_types=1);

namespace App\Enums;

final class EnrollmentCancellationReason
{
    public const TIMEOUT = 'timeout';

    public const USER_REQUEST = 'user-request';

    public const ACTIVITY_CANCELLED = 'activity-cancelled';

    public const ADMIN = 'admin';
}
