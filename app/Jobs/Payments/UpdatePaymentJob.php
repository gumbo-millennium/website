<?php

declare(strict_types=1);

namespace App\Jobs\Payments;

use App\Contracts\Payments\PaymentManager;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;

class UpdatePaymentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Payment $payment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PaymentManager $manager)
    {
        $payment = $this->payment;

        // Check if the payment can't be mutated anymore
        if ($payment->is_stable) {
            return;
        }

        // Check if the payment has a transaction_id
        if (! $payment->transaction_id) {
            return;
        }

        // Get the provider
        $provider = $manager->find($payment->provider);

        // Check the states
        if ($provider->isPaid($payment)) {
            $payment->paid_at = Date::now();
        }

        if ($provider->isExpired($payment)) {
            $payment->expired_at = Date::now();
        }

        if ($provider->isCancelled($payment)) {
            $payment->cancelled_at = Date::now();
        }

        $payment->save();
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }
}
