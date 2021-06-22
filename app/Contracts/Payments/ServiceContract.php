<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

use Mollie\Api\Resources\Order;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Refund;
use Mollie\Api\Resources\Shipment;

interface ServiceContract
{
    /**
     * Creates a Mollie order for the given model.
     */
    public function createOrder(PayableModel $model): Order;

    /**
     * Returns the URL to the Mollie dashboard.
     */
    public function getDashboardUrl(PayableModel $model): ?string;

    /**
     * Checks if an order is paid, does not mutate the $model.
     */
    public function isPaid(PayableModel $model): bool;

    /**
     * Returns the single payment that was succesfully completed.
     */
    public function getCompletedPayment(PayableModel $model): ?Payment;

    /**
     * Returns the URL to the payment URL, if any.
     */
    public function getRedirectUrl(PayableModel $model): string;

    /**
     * Issue a full refund.
     */
    public function refundAll(PayableModel $model): ?Refund;

    /**
     * Checks if an order is expired, does not mutate the $model.
     */
    public function isExpired(PayableModel $model): bool;

    /**
     * Checks if an order is shipped, does not mutate the $model.
     */
    public function isShipped(ShippableModel $model): bool;

    /**
     * Returns a single shipment, if there's any.
     */
    public function getShipment(ShippableModel $model): ?Shipment;

    /**
     * Send all products out for shipping. Optionally
     * specifies a tracking code.
     */
    public function shipAll(ShippableModel $model, ?string $carrier = null, ?string $trackingCode = null): ?Shipment;
}
