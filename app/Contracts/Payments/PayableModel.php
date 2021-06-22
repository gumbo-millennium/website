<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

use App\Services\Payments\Order;

interface PayableModel
{
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
}
