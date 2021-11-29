<?php

declare(strict_types=1);

namespace App\Jobs\Payments;

use App\Facades\Payments;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;

class CancelExpirePayments implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $query = Payment::query()
            ->whereNull('cancelled_at')
            ->whereNull('paid_at')
            ->where('expired_at', '<', Date::now())
            ->with(['payable'])
            ->get();
        foreach ($query as $payment) {
            // Try to cancel the payment
            Payments::cancel($payment);
        }
    }
}
