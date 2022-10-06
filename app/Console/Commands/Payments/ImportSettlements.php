<?php

declare(strict_types=1);

namespace App\Console\Commands\Payments;

use App\Helpers\Str;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Payments\Settlement;
use App\Models\Shop\Order as ShopOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
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
            $this->api->setApiKey($orgKey);
        }

        // Get start page
        $nextId = $this->option('all')
            ? null
            : Settlement::query()
                ->where('status', '!=', 'paidout')
                ->orderBy('created_at', 'desc')
                ->first('mollie_id')
                ?->mollie_id;

        // Keep iterating
        while ($settlements = $this->api->settlements()->page($nextId)) {
            // Import settlements
            /** @var MollieSettlement $settlement */
            foreach ($settlements as $settlement) {
                $model = $this->importSettlement($settlement);

                if ($this->verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->reportSettlement($model);
                }

                $nextId = $settlement->id;
            }

            // Stop if no next page is available
            if (! $settlements->hasNext()) {
                break;
            }
        }

        return 0;
    }

    /**
     * Imports a single settlement.
     */
    private function importSettlement(MollieSettlement $settlement): Settlement
    {
        // Find or start model
        /** @var Settlement $model */
        $model = Settlement::firstOrNew(['mollie_id' => $settlement->id]);

        // Fill required data
        $model->fill([
            'reference' => $settlement->reference,
            'status' => $settlement->status,
            'amount' => money_value($settlement->amount),
            'created_at' => $settlement->settledAt ? Date::parse($settlement->createdAt) : null,
            'settled_at' => $settlement->settledAt ? Date::parse($settlement->settledAt) : null,
        ]);

        // Find payments
        $settlementPayments = $this->mapPaymentsCollectionToPayments($settlement->payments());
        $paymentModels = $this->mapMolliePaymentsToPaymentModels($settlementPayments);

        // Attach payments
        $model->enrollments()->sync($this->determineModels(Enrollment::class, $settlementPayments, $paymentModels));

        // Attach shop orders
        $model->shopOrders()->sync($this->determineModels(ShopOrder::class, $settlementPayments, $paymentModels));

        // Save model
        $model->save();

        return $model;
    }

    /**
     * Report about the imported settlement.
     */
    private function reportSettlement(Settlement $settlement): void
    {
        $this->line(sprintf(
            'Settlement %s: %s',
            $settlement->reference,
            $settlement->status,
        ));

        if ($this->verbosity < OutputInterface::VERBOSITY_VERY_VERBOSE) {
            return;
        }

        /** @var Enrollment $enrollment */
        foreach ($settlement->enrollments()->with(['activity', 'user'])->get() as $enrollment) {
            $this->line(sprintf(
                'Enrollment of <comment>%s</> for <comment>%s</>; settled for <info>%s</>',
                $enrollment->user?->name ?? 'n/a',
                $enrollment->activity?->name ?? 'n/a',
                Str::price($enrollment->settlement->amount),
            ));
        }

        /** @var ShopOrder $order */
        foreach ($settlement->shopOrder()->with('user')->withCount('variants')->get() as $order) {
            $this->line(sprintf(
                'Shop Order <comment>%s</> by <comment>%s</> with <comment>%d</> product(s); settled for <info>%s</>',
                $order->number,
                $order->user?->name ?? 'n/a',
                $order->variants_count ?? 'n/a',
                Str::price($order->settlement->amount),
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
