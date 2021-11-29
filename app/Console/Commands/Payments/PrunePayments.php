<?php

declare(strict_types=1);

namespace App\Console\Commands\Payments;

use App\Contracts\Payments\Payable;
use App\Enums\PaymentStatus;
use App\Facades\Payments;
use App\Models\Enrollment;
use App\Models\Shop\Order;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Console\Output\OutputInterface;

class PrunePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel payments for models that won\'t be paid anymore';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prunedItems = 0;
        $prunedItems = $this->handleEnrollments();
        $prunedItems = $this->handleOrders();

        $this->info("Pruned {$prunedItems} items");
    }

    private function handleEnrollments(): int
    {
        $cursor = Enrollment::query()
            ->whereExpired()
            ->whereHas('payments', fn (Builder $query) => $query->pending())
            ->cursor();

        $this->comment('Pruning enrollments…', OutputInterface::VERBOSITY_VERBOSE);

        $result = $this->handleCollection($cursor);

        $this->line(sprintf('Pruned <info>%d</> enrollment payments', $result));

        return $result;
    }

    private function handleOrders(): int
    {
        $cursor = Order::query()
            ->withoutGlobalScopes()
            ->where(function (Builder $query) {
                $query
                    ->orWhereNotNull('paid_at')
                    ->orWhereNotNull('cancelled_at')
                    ->orWhere(
                        fn ($query) => $query
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', Date::now()),
                    );
            })
            ->whereHas('payments', fn (Builder $query) => $query->pending())
            ->cursor();

        $this->comment('Pruning orders…', OutputInterface::VERBOSITY_VERBOSE);

        $result = $this->handleCollection($cursor);

        $this->line(sprintf('Pruned <info>%d</> order payments', $result));

        return $result;
    }

    private function handleCollection(iterable $collection): int
    {
        $cancelCount = 0;

        /** @var Payable $model */
        foreach ($collection as $model) {
            $name = sprintf('<comment>%s</> <info>%s</>', class_basename($model), $model->id);

            $this->line(sprintf('Cancelling payments for %s', $name), null, OutputInterface::VERBOSITY_DEBUG);

            foreach ($model->payments()->pending()->get() as $payment) {
                $paymentStatus = $payment->status;

                $this->line(sprintf('Found payment <info>%s</> (%s)', $payment->id, $paymentStatus), null, OutputInterface::VERBOSITY_DEBUG);

                if ($payment->is_stable) {
                    continue;
                }

                $this->line("Cancelling payment for ${name}…", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

                if ($payment->status === PaymentStatus::OPEN) {
                    $this->line("Requesting cancellation for ${name} on <info>{$payment->service}</>…", null, OutputInterface::VERBOSITY_DEBUG);

                    Payments::find($payment->service)->cancel($payment);
                }

                $payment->cancelled_at ??= Date::now();
                $payment->save();

                $cancelCount++;
            }
        }

        return $cancelCount;
    }
}
