<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;

return [
    'payment-status' => [
        PaymentStatus::PENDING => 'In behandeling',
        PaymentStatus::OPEN => 'Open',
        PaymentStatus::PAID => 'Betaald',
        PaymentStatus::CANCELLED => 'Geannuleerd',
        PaymentStatus::EXPIRED => 'Verlopen',
    ],
];
