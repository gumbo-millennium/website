<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Payments\PayableModel;
use App\Contracts\Payments\ServiceContract as PaymentServiceContract;
use App\Helpers\Arr;
use App\Helpers\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Refund;
use Mollie\Api\Resources\Shipment;
use Mollie\Laravel\Facades\Mollie;
use RuntimeException;

class PaymentService implements PaymentServiceContract
{
    private const LOCAL_URLS = [
        'localhost',
        '127.0.0.1',
        '[::1]',
        '*.test',
    ];

    /**
     * @var array<Order> $cachedOrders
     */
    private array $cachedOrders = [];

    /**
     * Locates an order and stores the result in a request cache.
     */
    public function findOrder(PayableModel $model): ?Order
    {
        $paymentId = $model->{$model->getPaymentIdField()};

        if (! $paymentId) {
            return null;
        }

        if (array_key_exists($paymentId, $this->cachedOrders)) {
            return $this->cachedOrders[$paymentId];
        }

        Log::info('Using Mollie API', [
            'api' => Mollie::api()->getApiEndpoint(),
            'key' => Config::get('mollie'),
        ]);

        try {
            $order = Mollie::api()->orders->get($paymentId, [
                'embed' => 'payments,shipments',
            ]);

            return $this->cachedOrders[$paymentId] = $order;
        } catch (ApiException $apiException) {
            if ($apiException->getCode() === 404) {
                return $this->cachedOrders[$paymentId] = null;
            }

            throw new RuntimeException(
                "API call to Mollie failed: {$apiException->getMessage()}",
                $apiException->getCode(),
                $apiException,
            );
        }
    }

    public function createOrder(PayableModel $model): Order
    {
        $order = $model->toMollieOrder();

        if (Str::is(self::LOCAL_URLS, parse_url(URL::full(), PHP_URL_HOST))) {
            unset($order['webhookUrl']);
        }

        $order = Mollie::api()->orders->create($order->toArray(), [
            'embed' => 'payments,shipments',
        ]);

        return $this->cachedOrders[$order->id] = $order;
    }

    public function cancelOrder(PayableModel $model): void
    {
        // Find order
        if (! $order = $this->findOrder($model)) {
            return;
        }

        // Cancel if possible
        if ($order->isCancelable) {
            $order->cancel();
        }
    }

    public function isPaid(PayableModel $model): bool
    {
        // Check local first
        if ($model->{$model->getPaidAtField()} !== null) {
            return true;
        }

        // Find order
        if (! $order = $this->findOrder($model)) {
            return false;
        }

        return $order->isPaid();
    }

    public function isCompleted(PayableModel $model): bool
    {
        // Check local first
        if ($model->{$model->getCompletedAtField()} !== null) {
            return true;
        }

        // Find order
        if (! $order = $this->findOrder($model)) {
            return false;
        }

        return $order->isCompleted();
    }

    public function isCancelled(PayableModel $model): bool
    {
        // Check local first
        if ($model->{$model->getCancelledAtField()} !== null) {
            return true;
        }

        // Find order
        if (! $order = $this->findOrder($model)) {
            return false;
        }

        return $order->isCanceled() || $order->isExpired();
    }

    public function getDashboardUrl(PayableModel $model): ?string
    {
        // Find order
        if (! $order = $this->findOrder($model)) {
            return null;
        }

        $link = object_get($order->_links, 'dashboard._href')
            ?? object_get($order->_links, 'dashboard');

        return is_string($link) ? $link : null;
    }

    public function getRedirectUrl(PayableModel $model): ?string
    {
        // Paid orders don't have a redirect URL
        if ($model->paid_at !== null) {
            return null;
        }

        // Find order
        if (! $order = $this->findOrder($model)) {
            return null;
        }

        // Paid or cancelled, cannot be paid anymore.
        if (
            $order->isPaid()
            || $order->isCanceled()
            || $order->isExpired()
        ) {
            return null;
        }

        // Check for a checkout URL, might be null in case
        // the payment was cancelled or has failed.
        $existingUrl = $order->getCheckoutUrl();
        if ($existingUrl) {
            return $existingUrl;
        }

        // Create a new payment with the same options
        /** @var Payment $payment */
        $payment = $order->createPayment([]);

        // Return new URL
        return $payment->getCheckoutUrl();
    }

    public function getCompletedPayment(PayableModel $model): ?Payment
    {
        // Find order
        if (! $order = $this->findOrder($model)) {
            return null;
        }

        // Only works if the order is paid
        if (! $order->isPaid()) {
            return null;
        }

        // Find the first
        return Arr::first(
            $order->payments() ?? [],
            fn (Payment $payment) => $payment->isPaid(),
        );
    }

    public function getShipment(PayableModel $model): ?Shipment
    {
        // Find order
        if (! $order = $this->findOrder($model)) {
            return null;
        }

        // Only works if the order is shipped
        if (! ($order->isShipping() && $order->isCompleted())) {
            return null;
        }

        /** @var Shipment */
        return Arr::first($order->shipments());
    }

    public function shipAll(PayableModel $model, ?string $carrier = null, ?string $trackingCode = null): ?Shipment
    {
        // Find order
        if (! $order = $this->findOrder($model)) {
            return null;
        }

        // Check if cancelled
        if ($order->isCanceled()) {
            return null;
        }

        // Find any shipments (shipping = some is sent, completed = all is sent)
        if ($order->isShipping() || $order->isCompleted()) {
            return Arr::first($order->shipments());
        }

        // Ship everything
        return $order->shipAll([
            '',
        ]);
    }

    public function refundAll(PayableModel $model): ?Refund
    {
        // Find order
        if (! $order = $this->findOrder($model)) {
            return null;
        }

        // Check if shipped
        if ($order->isShipping() || ! $order->isPaid()) {
            return null;
        }

        // Check if refunded
        if ($order->amountRefunded >= $order->amount) {
            return Arr::first($order->refunds());
        }

        // Refund everything
        return $order->refundAll();
    }
}
