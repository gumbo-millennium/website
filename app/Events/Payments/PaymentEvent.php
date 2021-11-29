<?php

declare(strict_types=1);

namespace App\Events\Payments;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class PaymentEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected Payment $payment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment->withoutRelations();
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }
}
