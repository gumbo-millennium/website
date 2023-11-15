<?php

declare(strict_types=1);

namespace App\Console\Commands\Payments;

use App\Helpers\Str;
use App\Jobs\Payments\UpdateMollieSettlement;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Payments\Settlement;
use App\Models\Shop\Order as ShopOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Mollie\Api\Resources\Payment as MolliePayment;
use Mollie\Api\Resources\PaymentCollection;
use Mollie\Api\Resources\Settlement as MollieSettlement;
use Mollie\Laravel\Facades\Mollie;
use Mollie\Laravel\Wrappers\MollieApiWrapper;
use Symfony\Component\Console\Output\OutputInterface;

class ImportSettlements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        payments:settlements
            {--all : Import and update all settlements}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates settlements from Mollie';

    /**
     * Mollie API.
     */
    protected MollieApiWrapper $api;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var MollieApiWrapper $api */
        $this->api = Mollie::api();

        // Switch the API to the org key
        if ($orgKey = Config::get('mollie.org_key')) {
            $this->api->setAccessToken($orgKey);
        }

        $finalSettlementId = null;
        if (! $this->option('all')) {
            $finalSettlementId = Settlement::query()
                ->whereNotNull('settled_at')
                ->orderByDesc('created_at')
                ->value('mollie_id');
        }

        // Iterate while we're not out of pages
        $nextPageId = null;
        while ($settlements = $this->api->settlements()->page($nextPageId, 50)) {
            /** @var MollieSettlement $settlement */
            foreach ($settlements as $index => $settlement) {
                // Skip empty rows
                if (! $settlement->id) {
                    $this->line("Skipping settlement {$index} with no ID", null, OutputInterface::VERBOSITY_DEBUG);

                    continue;
                }

                // Stop pagination entirely when the expected settlement is found (never used on --all)
                if ($settlement->id === $finalSettlementId) {
                    $this->line("Found final settlement {$finalSettlementId}, stopping pagination", null, OutputInterface::VERBOSITY_DEBUG);

                    break 2;
                }

                $this->line("Importing settlement <info>{$settlement->id}</>", null, OutputInterface::VERBOSITY_DEBUG);

                UpdateMollieSettlement::dispatchSync($settlement->id);

                $this->info('Import completed', OutputInterface::VERBOSITY_DEBUG);

                if ($this->output->isVerbose()) {
                    $this->reportSettlement(Settlement::firstWhere('mollie_id', $settlement->id));
                }

                $nextPageId = $settlement->id;
            }

            // Stop if no next page is available
            if (! $settlements->hasNext()) {
                break;
            }
        }

        return 0;
    }

    /**
     * Report about the imported settlement.
     */
    private function reportSettlement(Settlement $settlement): void
    {
        $this->line(sprintf(
            'Settlement <fg=green>%s</>: %s',
            $settlement->reference,
            $settlement->status === 'paidout' ? "Paid out on <fg=magenta>{$settlement->settled_at->isoFormat('D MMM YYYY')}</>" : 'Not paid out yet',
        ));

        if (! $this->output->isVeryVerbose()) {
            return;
        }

        $settlementPayments = $settlement->payments()->with('payable')->get();
        $settlementEnrollments = $settlementPayments->filter(fn (Payment $payment) => $payment->payable instanceof Enrollment);
        $settlementShopOrders = $settlementPayments->filter(fn (Payment $payment) => $payment->payable instanceof ShopOrder);

        /** @var Payment $payment */
        foreach ($settlementEnrollments as $payment) {
            $enrollment = $payment->payable;

            $this->line(sprintf(
                'Enrollment of <fg=green>%s</> for <fg=cyan>%s</>; settled for <fg=gray>%s</>',
                $enrollment->user?->name ?? 'n/a',
                $enrollment->activity?->name ?? 'n/a',
                Str::price($enrollment->settlement->amount),
            ));
        }

        /** @var Payment $payment */
        foreach ($settlementShopOrders as $payment) {
            $order = $payment->payable;

            $this->line(sprintf(
                'Shop Order <fg=green>%s</> by <fg=cyan>%s</> with <fg=yellow>%d</> product(s); settled for <fg=gray>%s</>',
                $order->number,
                $order->user?->name ?? 'n/a',
                $order->variants_count ?? 'n/a',
                Str::price($order->settlement->amount),
            ));
        }

        foreach ($settlement->missing_payments as $payment) {
            $this->line(sprintf(
                'Unkown payment <fg=green>%s</> of <fg=gray>%s</>, settled for <fg=gray>%s</>',
                $payment['id'],
                Str::price(money_value($payment['amount'])),
                Str::price(money_value($payment['settlementAmount'])),
            ));
        }

        foreach ($settlement->missing_refunds as $refund) {
            $this->line(sprintf(
                'Unkown refund <fg=green>%s</> of <fg=gray>%s</>, settled for <fg=gray>%s</>',
                $refund['id'],
                Str::price(money_value($refund['amount'])),
                Str::price(money_value($refund['settlementAmount'])),
            ));
        }
    }

    /**
     * Determine models for a set of Mollie Payments and Payment models.
     * @param Collection<string,MolliePayment> $settlementPayments
     * @param Collection<string,Payment> $paymentModels
     * @return array<int,array>
     */
    private function determineModels(string $model, Collection $settlementPayments, Collection $paymentModels): array
    {
        $models = $model::query()
            ->whereHas('payments', fn ($query) => $query->whereIn('id', $paymentModels->pluck('id')))
            ->get();

        $matchedModels = [];
        foreach ($settlementPayments as $payment) {
            // Skip payments without orderId
            if (! $orderId = $payment->orderId) {
                continue;
            }

            // Find enrollment
            $model = $models->first(fn ($model) => $model->payments->contains('provider_id', $orderId));
            if (! $model) {
                continue;
            }

            $matchedModels[$model->id] = [
                'amount' => money_value($payment->amount),
            ];
        }

        return $matchedModels;
    }

    /**
     * Return all payments as models, instead of in a magic collection.
     * @return Collection<MolliePayment>|MolliePayment[]
     */
    private function mapPaymentsCollectionToPayments(PaymentCollection $paymentCollection): Collection
    {
        $settlementPayments = Collection::make();
        do {
            foreach ($paymentCollection as $payment) {
                $settlementPayments->put($payment->id, $payment);
            }
        } while ($paymentCollection->hasNext() && $paymentCollection = $paymentCollection->next());

        return $settlementPayments;
    }

    /**
     * Resolves Mollie Payments to our Payment models, which are mapped to orders.
     * @param Collection<MolliePayment> $settlementPayments
     * @return Collection<string,Payment>|Payment[]
     */
    private function mapMolliePaymentsToPaymentModels(Collection $settlementPayments): Collection
    {
        // Get all payment IDs on their order ID
        $paymentIdsByOrder = [];
        do {
            /** @var MolliePayment $payment */
            foreach ($settlementPayments as $payment) {
                if ($payment->orderId === null) {
                    continue;
                }

                $paymentIdsByOrder[$payment->orderId] = $payment->id;
            }
        } while ($settlementPayments->hasNext() && $settlementPayments = $settlementPayments->next());

        // Get payments
        $payments = Payment::query()
            ->where('provider', 'mollie')
            ->whereIn('provider_id', array_keys($paymentIdsByOrder))
            ->get()
            ->keyBy('provider_id');

        // Remap payments
        $result = Collection::make();
        foreach ($paymentIdsByOrder as $orderId => $paymentId) {
            $result->put($paymentId, $payments->get($orderId));
        }

        // Skip null-values
        return $result->filter();
    }
}
