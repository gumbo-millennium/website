<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\Payments as Events;
use App\Models\Payment;
use Illuminate\Support\Facades\Date;

class PaymentObserver
{
    /**
     * Handle payment changes.
     *
     * @param \App\Payment $payment
     * @return void
     */
    public function saved(Payment $payment)
    {
        // Check if the payment was just assigned an ID
        if ($payment->wasChanged('transaction_id') && $payment->transaction_id) {
            Events\PaymentCreated::dispatch($payment);
        }

        // Check if the payment was paid,
        if ($payment->wasChanged('paid_at') && $payment->paid_at !== null) {
            Events\PaymentPaid::dispatch($payment);
        }

        // … or expired,
        if ($payment->wasChanged('expired_at') && $payment->expired_at !== null && $payment->expired_at < Date::now()) {
            Events\PaymentExpired::dispatch($payment);
        }

        // … or cancelled
        if ($payment->wasChanged('cancelled_at') && $payment->cancelled_at !== null) {
            Events\PaymentCancelled::dispatch($payment);
        }
    }
}
