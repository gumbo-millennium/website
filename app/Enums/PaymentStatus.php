<?php

declare(strict_types=1);

namespace App\Enums;

final class PaymentStatus
{
    public const PENDING = 'pending';

    public const OPEN = 'open';

    public const PAID = 'paid';

    public const CANCELLED = 'cancelled';

    public const EXPIRED = 'expired';

    public const STABLE_STATES = [
        self::PAID,
        self::CANCELLED,
        self::EXPIRED,
    ];
}
