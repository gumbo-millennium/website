<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Payments\PaymentPaid;
use App\Events\Shop as ShopEvents;
use App\Models\Shop\Order;
use Illuminate\Support\Facades\Date;

class PaymentPaidListener
{
    /**
     * Handle the event.
     */
    public function handle(PaymentPaid $event): void
    {
        // Get subject
        $payment = $event->getPayment();
        $subject = $payment->payable;

        if (! $subject instanceof Order) {
            return;
        }

        $subject->paid_at = Date::now();
        $subject->save();

        ShopEvents\OrderPaidEvent::dispatch($subject);
    }
}
