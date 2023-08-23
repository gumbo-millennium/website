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

    'bot-quotes' => [
        'titles' => [
            'week' => 'The quotes of week :week1 :year1',
            'month' => 'The quotes of :month1 :year1',
            'adjacent-months' => 'The quotes of :month1 :year1 and :month2 :year2',
            'spanning-months' => 'The quotes of :month1 :year1 - :month2 :year2',
        ],
    ],
];
