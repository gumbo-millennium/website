<?php

declare(strict_types=1);

namespace App\Enums;

enum EnrollmentCancellationReason: string
{
    case TIMEOUT = 'timeout';
    case USER_REQUEST = 'user-request';
    case ACTIVITY_CANCELLED = 'activity-cancelled';
    case ADMIN = 'admin';
    case DELETION = 'account-deletion';
}
