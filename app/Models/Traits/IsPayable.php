<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Contracts\Payments\PayableModel;
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

    /**
     * Returns the field to store when the object was shipped.
     */
    public function getCompletedAtField(): string
    {
        return 'completed_at';
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->{$this->getCancelledAtField()} !== null) {
            return PayableModel::STATUS_CANCELLED;
        }

        if ($this->{$this->getPaymentIdField()} === null) {
            return PayableModel::STATUS_UNKNOWN;
        }

        if ($this->{$this->getCompletedAtField()} !== null) {
            return PayableModel::STATUS_COMPLETED;
        }

        if ($this->{$this->getPaidAtField()} !== null) {
            return PayableModel::STATUS_PAID;
        }

        return PayableModel::STATUS_OPEN;
    }
}
