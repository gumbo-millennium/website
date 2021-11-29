<?php

declare(strict_types=1);

namespace App\Facades;

use App\Contracts\Payments\PaymentManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static null|\App\Contracts\Payments\PaymentService find(string $service)
 * @method static \App\Contracts\Payments\PaymentService default()
 * @method static string getDefault()
 *
 * @method static Payment create(\App\Contracts\Payments\Payable $payable)
 * @method static string|null nextUrl(\App\Models\Payment $payment)
 * @method static void cancel(\App\Models\Payment $payment)
 * @method static bool isPaid(\App\Models\Payment $payment)
 * @method static bool isExpired(\App\Models\Payment $payment)
 * @method static bool isCancelled(\App\Models\Payment $payment)
 *
 * @see \App\Contracts\Payments\PaymentManager
 * @see \App\Contracts\Payments\PaymentService
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
        return PaymentManager::class;
    }
}
