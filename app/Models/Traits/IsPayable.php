<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Services\Payments\Order;

trait IsPayable
{
    /**
     * Returns a Mollie order from this model.
     */
    abstract public function toMollieOrder(): Order;

    /**
     * Returns the field to store the payment ID in.
     */
    public function getPaymentIdField(): string
    {
        return 'payment_id';
    }

    /**
     * Returns the field to store when the object was paid.
     */
    public function getPaidAtField(): string
    {
        return 'paid_at';
    }

    /**
     * Returns the field to store when the object was cancelled.
     */
    public function getCancelledAtField(): string
    {
        return 'cancelled_at';
    }
}
