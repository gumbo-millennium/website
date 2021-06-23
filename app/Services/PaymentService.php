<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Payments\PayableModel;
use App\Contracts\Payments\ServiceContract as PaymentServiceContract;
use App\Helpers\Arr;
use Doctrine\Common\Cache\Psr6\InvalidArgument;
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
use UnexpectedValueException;

class PaymentService implements PaymentServiceContract
{
    /**
     * @var array<Order> $orders
     */
    private array $cachedOrders = [];

    /**
     * Locates an order and stores the result in a request cache.
     */
    public function findOrder(PayableModel $model): Order
    {
        $paymentId = $model->{$model->getPaymentIdField()};

        if (! $paymentId) {
            throw new UnexpectedValueException('No Mollie order for this model (yet).', 404);
        }

        if (isset($this->cachedOrders[$paymentId])) {
            return $this->cachedOrders[$paymentId];
        }

        Log::info('Using Mollie API', [
            'api' => Mollie::api()->getApiEndpoint(),
            'key' => Config::get('mollie'),
        ]);

        try {
            $order = Mollie::api()->orders->get($paymentId, [
                'embed' => [
                    'payments',
                    'shipments',
                ],
            ]);

            return $this->cachedOrders[$paymentId] = $order;
        } catch (ApiException $apiException) {
            throw new RuntimeException(
                "API call to Mollie failed: {$apiException->getMessage()}",
                $apiException->getCode(),
                $apiException,
            );
        }

        return $this->cachedOrders[$paymentId];
    }

    public function createOrder(PayableModel $model): Order
    {
        $order = $model->toMollieOrder();

        if (in_array(parse_url(URL::full(), PHP_URL_HOST), [
            'localhost',
            '127.0.0.1',
            '[::1]',
        ], true)) {
            unset($order['webhookUrl']);
        }

        return Mollie::api()->orders->create($order->toArray(), [
            'embed' => ['payments'],
        ]);
    }

    public function isPaid(PayableModel $model): bool
    {
        if ($model->{$model->getPaidAtField()} !== null) {
            return true;
        }

        return $this->findOrder($model)->isPaid();
    }

    public function isCompleted(PayableModel $model): bool
    {
        if ($model->{$model->getCompletedAtField()} !== null) {
            return true;
        }

        return $this->findOrder($model)->isCompleted();
    }

    public function isCancelled(PayableModel $model): bool
    {
        if ($model->{$model->getCancelledAtField()} !== null) {
            return true;
        }

        $order = $this->findOrder($model);

        return $order->isCanceled() || $order->isExpired();
    }

    public function getDashboardUrl(PayableModel $model): ?string
    {
        $order = $this->findOrder($model);

        return object_get($order->_links, 'dashboard');
    }

    public function getRedirectUrl(PayableModel $model): string
    {
        // Paid orders don't have a redirect URL
        if ($model->paid_at !== null) {
            return null;
        }

        $order = $this->findOrder($model);

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
        try {
            $order = $this->findOrder($model);

            if (! $order->isPaid()) {
                return null;
            }

            return Arr::first(
                $order->payments(),
                fn (Payment $payment) => $payment->isPaid(),
            );
        } catch (InvalidArgument $exception) {
            return null;
        }
    }

    public function getShipment(PayableModel $model): ?Shipment
    {
        try {
            $order = $this->findOrder($model);

            if (! $order->isShipping()) {
                return null;
            }

            /** @var Shipment */
            return Arr::first($order->shipments());
        } catch (InvalidArgument $exception) {
            return null;
        }
    }

    public function shipAll(PayableModel $model, ?string $carrier = null, ?string $trackingCode = null): ?Shipment
    {
        // Find order
        $mollieOrder = $this->findOrder($model);

        // Check if cancelled
        if ($mollieOrder->isCanceled()) {
            return null;
        }

        // Find any shipments (shipping = some is sent, completed = all is sent)
        if ($mollieOrder->isShipping() || $mollieOrder->isCompleted()) {
            return Arr::first($mollieOrder->shipments());
        }

        // Ship everything
        return $mollieOrder->shipAll([
            '',
        ]);
    }

    public function refundAll(PayableModel $model): ?Refund
    {
        // Find order
        $order = $this->findOrder($model);

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
