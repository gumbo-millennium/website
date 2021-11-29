<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;
use App\Models\ActivityMessage;

return [
    'target-audiences' => [
        ActivityMessage::AUDIENCE_ANY => 'Confirmed enrolled',
        ActivityMessage::AUDIENCE_CONFIRMED => 'Unconfirmed enrollments',
        ActivityMessage::AUDIENCE_PENDING => 'Both confirmed and unconfirmed enrollments',
    ],

    'payment-status' => [
        PaymentStatus::PENDING => 'Pending',
        PaymentStatus::OPEN => 'Open',
        PaymentStatus::PAID => 'Paid',
        PaymentStatus::CANCELLED => 'Cancelled',
        PaymentStatus::EXPIRED => 'Expired',
    ],
];
