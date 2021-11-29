<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;
use App\Models\ActivityMessage;

return [
    'target-audiences' => [
        ActivityMessage::AUDIENCE_ANY => 'Bevestigde inschrijvingen',
        ActivityMessage::AUDIENCE_CONFIRMED => 'Niet-bevestigde inschrijvingen',
        ActivityMessage::AUDIENCE_PENDING => 'Zowel bevestigde als niet-bevestigde inschrijvingen',
    ],

    'payment-status' => [
        PaymentStatus::PENDING => 'In behandeling',
        PaymentStatus::OPEN => 'Open',
        PaymentStatus::PAID => 'Betaald',
        PaymentStatus::CANCELLED => 'Geannuleerd',
        PaymentStatus::EXPIRED => 'Verlopen',
    ],
];
