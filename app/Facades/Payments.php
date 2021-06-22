<?php

declare(strict_types=1);

namespace App\Facades;

use App\Contracts\Payments\PayableModel;
use App\Contracts\Payments\ServiceContract as PaymentServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isPaid(PayableModel $model)
 * @method static bool isExpired(PayableModel $model)
 * @method static bool isShipped(ShippableModel $model)
 *
 * @method static \Mollie\Api\Resources\Payment|null getCompletedPayment(PayableModel $model)
 * @method static \Mollie\Api\Resources\Shipment|null getShipment(ShippableModel $model)
 *
 * @method static null|string getDashboardUrl(PayableModel $model)
 * @method static string getRedirectUrl(PayableModel $model)
 *
 * @method static \Mollie\Api\Resources\Order createOrder(PayableModel $model)
 * @method static \Mollie\Api\Resources\Refund|null refundAll(PayableModel $model)
 * @method static \Mollie\Api\Resources\Shipment|null shipAll(ShippableModel $model, ?string $carrier = null, ?string $trackingCode = null)
 *
 * @see \App\Contracts\Payments\ServiceContract
 * @see \App\Services\PaymentService
 */
class Payments extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PaymentServiceContract::class;
    }
}
