<?php

declare(strict_types=1);

namespace App\Console\Commands\Payments;

use App\Jobs\Payments\UpdatePaymentJob;
use App\Models\Payment;
use Illuminate\Console\Command;

class UpdatePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        payments:update
            {id? : ID of the payment to update}
            {--all : Update all pending payments}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $all = $this->option('all');

        if (! $id && ! $all) {
            $this->error('Please specify an id or --all');

            return;
        }

        $payments = Payment::query()
            ->when($id, fn ($query) => $query->where('id', $id))
            ->when($all, fn ($query) => $query->pending());

        $payments->chunk(100, function ($models) {
            foreach ($models as $payment) {
                $this->info('Updating payment: ' . $payment->id);
                UpdatePaymentJob::dispatchNow($payment);
            }
        });
    }
}
