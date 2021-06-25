<?php

declare(strict_types=1);

namespace Tests\Fixtures\Services;

use App\Contracts\Payments\PayableModel;
use App\Contracts\Payments\ServiceContract as PaymentServiceContract;
use App\Helpers\Str;
use App\Services\PaymentService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Date;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Order;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Refund;
use Mollie\Api\Resources\Shipment;
use Mollie\Api\Types\OrderStatus;
use RuntimeException;

class DummyPaymentService extends PaymentService implements PaymentServiceContract
{
    private array $orderCache = [];

    private MollieApiClient $api;

    public function __construct()
    {
        $this->api = (new MollieApiClient())
            ->setApiKey(sprintf('test_%s', Str::random(35)));
    }

    public function findOrder(PayableModel $model): ?Order
    {
        if (! $paymentId = $model->{$model->getPaymentIdField()}) {
            return null;
        }

        return $this->orderCache[$paymentId] ?? null;
    }

    public function createOrder(PayableModel $model): Order
    {
        $orderData = $model->toMollieOrder();

        $paymentId = $orderData->id = Str::random(64);
        $mollieOrder = new Order($this->api);
        foreach ($orderData as $property => $value) {
            $mollieOrder->{$property} = $value instanceof Arrayable ? $value->toArray() : $value;
        }

        $payment = new Payment($this->api);

        $mollieOrder->id = sprintf('test_order_%s', Str::random(20));
        $mollieOrder->mode = 'test';
        $mollieOrder->_embedded = (object) [
            'payments' => [$payment],
        ];

        return $this->orderCache[$paymentId] = $mollieOrder;
    }

    public function cancelOrder(PayableModel $model): void
    {
        if (! $payment = $this->findOrder($model)) {
            return;
        }

        $payment->isCancelable = false;
        $payment->canceledAt = Date::today()->toIso8601ZuluString();
        $payment->status === OrderStatus::STATUS_CANCELED;
    }

    public function getDashboardUrl(PayableModel $model): ?string
    {
        if (! $order = $this->findOrder($model)) {
            return null;
        }

        return sprintf('https://example.com/dashboard/%s', $order->id);
    }

    public function getRedirectUrl(PayableModel $model): string
    {
        if (! $order = $this->findOrder($model)) {
            throw new RuntimeException(
                'Failed to find redirect',
            );
        }

        return sprintf('https://example.com/dashboard/%s', $order->id);
    }

    public function refundAll(PayableModel $model): ?Refund
    {
        return null;
    }

    public function shipAll(PayableModel $model, ?string $carrier = null, ?string $trackingCode = null): ?Shipment
    {
        return null;
    }

    public function addDummyOrder(Order $order): void
    {
        $this->orderCache[$order->id] = $order;
    }
}
