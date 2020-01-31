<?php

namespace App\Jobs\Stripe;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Enrollment
     * @var \App\Models\Enrollment
     */
    protected $enrollment;

    /**
     * Create new job for this enrollment
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(StripeServiceContract $service)
    {
        $enrollment = $this->enrollment;

        if (empty($enrollment->total_price)) {
            return null;
        }

        $invoice = $service->getInvoice($enrollment);
        logger()->info('Created invoice.', compact('invoice'));
    }
}
