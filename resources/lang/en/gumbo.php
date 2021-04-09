<?php

declare(strict_types=1);

use App\Models\ActivityMessage;

return [
    'target-audiences' => [
        ActivityMessage::AUDIENCE_ANY => 'Confirmed enrolled',
        ActivityMessage::AUDIENCE_CONFIRMED => 'Unconfirmed enrollments',
        ActivityMessage::AUDIENCE_PENDING => 'Both confirmed and unconfirmed enrollments',
    ],
];
