<?php

declare(strict_types=1);

namespace App\Facades;

use App\Services\PaymentService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Mollie\Api\Resources\Invoice createForOrder(Order $order)
 * @method static string getRedirectUrl(Order $order)
 * @method static bool isPaid(Order $order)
 * @method static null|\DateTimeInterface paidAt(Order $order)
 *
 * @see \App\Facades\PaymentService
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
        return PaymentService::class;
    }
}
