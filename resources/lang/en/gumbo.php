<?php

declare(strict_types=1);

use App\Contracts\Payments\PayableModel;
use App\Models\ActivityMessage;

return [
    'target-audiences' => [
        ActivityMessage::AUDIENCE_ANY => 'Confirmed enrolled',
        ActivityMessage::AUDIENCE_CONFIRMED => 'Unconfirmed enrollments',
        ActivityMessage::AUDIENCE_PENDING => 'Both confirmed and unconfirmed enrollments',
    ],

    'payment-status' => [
        PayableModel::STATUS_UNKNOWN => 'Unknown',
        PayableModel::STATUS_OPEN => 'Open',
        PayableModel::STATUS_PAID => 'Paid',
        PayableModel::STATUS_CANCELLED => 'Cancelled',
        PayableModel::STATUS_COMPLETED => 'Completed',
    ],
];
