<?php

declare(strict_types=1);

use App\Contracts\Payments\PayableModel;
use App\Models\ActivityMessage;

return [
    'target-audiences' => [
        ActivityMessage::AUDIENCE_ANY => 'Bevestigde inschrijvingen',
        ActivityMessage::AUDIENCE_CONFIRMED => 'Niet-bevestigde inschrijvingen',
        ActivityMessage::AUDIENCE_PENDING => 'Zowel bevestigde als niet-bevestigde inschrijvingen',
    ],

    'payment-status' => [
        PayableModel::STATUS_UNKNOWN => 'Onbekend',
        PayableModel::STATUS_OPEN => 'Open',
        PayableModel::STATUS_PAID => 'Betaald',
        PayableModel::STATUS_CANCELLED => 'Geannuleerd',
        PayableModel::STATUS_COMPLETED => 'Afgerond',
    ],
];
