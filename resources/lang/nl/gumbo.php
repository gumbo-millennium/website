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

    'bot-quotes' => [
        'titles' => [
            'week' => 'De quotes van week :week1 :year1',
            'month' => 'De quotes van :month1 :year1',
            'adjacent-months' => 'De quotes van :month1 :year1 en :month2 :year2',
            'spanning-months' => 'De quotes van :month1 :year1 - :month2 :year2',
        ],
    ],
];
