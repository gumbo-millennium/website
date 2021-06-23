<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

use App\Services\Payments\Order;

interface PayableModel
{
    public const STATUS_UNKNOWN = 'unknown';

    public const STATUS_OPEN = 'open';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    /**
     * Returns a Mollie order from this model.
     */
    public function toMollieOrder(): Order;

    /**
     * Returns the field to store the payment ID in.
     */
    public function getPaymentIdField(): string;

    /**
     * Returns the field to store when the object was paid.
     */
    public function getPaidAtField(): string;

    /**
     * Returns the field to store when the object was cancelled.
     */
    public function getCancelledAtField(): string;

    /**
     * Returns the field to store when the order was completed.
     */
    public function getCompletedAtField(): string;
}
