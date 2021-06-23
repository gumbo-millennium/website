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
     * Reeturns the Mollie order for the given model.
     *
     * @throws RuntimeException if $model has no associated order
     */
    public function findOrder(PayableModel $model): Order;

    /**
     * Creates a Mollie order for the given model.
     */
    public function createOrder(PayableModel $model): Order;

    /**
     * Checks if an order is paid, does not mutate the $model.
     */
    public function isPaid(PayableModel $model): bool;

    /**
     * Checks if an order is completed, does not mutate the $model.
     */
    public function isCompleted(PayableModel $model): bool;

    /**
     * Checks if an order is expired, does not mutate the $model.
     */
    public function isCancelled(PayableModel $model): bool;

    /**
     * Returns the URL to the Mollie dashboard.
     */
    public function getDashboardUrl(PayableModel $model): ?string;

    /**
     * Returns the URL to the payment URL, if any.
     */
    public function getRedirectUrl(PayableModel $model): string;

    /**
     * Returns the single payment that was succesfully completed.
     */
    public function getCompletedPayment(PayableModel $model): ?Payment;

    /**
     * Issue a full refund.
     */
    public function refundAll(PayableModel $model): ?Refund;

    /**
     * Returns a single shipment, if there's any.
     */
    public function getShipment(PayableModel $model): ?Shipment;

    /**
     * Send all products out for shipping. Optionally
     * specifies a tracking code.
     */
    public function shipAll(PayableModel $model, ?string $carrier = null, ?string $trackingCode = null): ?Shipment;
}
