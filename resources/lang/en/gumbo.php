<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;

return [
    'payment-status' => [
        PaymentStatus::PENDING => 'Pending',
        PaymentStatus::OPEN => 'Open',
        PaymentStatus::PAID => 'Paid',
        PaymentStatus::CANCELLED => 'Cancelled',
        PaymentStatus::EXPIRED => 'Expired',
    ],
];
