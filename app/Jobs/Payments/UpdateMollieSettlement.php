<?php

declare(strict_types=1);

namespace App\Jobs\Payments;

use App\Models\Payment;
use App\Models\Payments\Settlement;
use Brick\Math\RoundingMode;
use Brick\Money\Context\CustomContext;
use Brick\Money\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment as MolliePayment;
use Mollie\Api\Resources\Refund as MollieRefund;
use Mollie\Api\Resources\Settlement as MollieSettlement;
use Mollie\Laravel\Wrappers\MollieApiWrapper;
use RuntimeException;

/**
 * @method static PendingDispatch dispatch(string $settlementId)
 * @method static PendingDispatch dispatchNow(string $settlementId)
 */
class UpdateMollieSettlement implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Determines the total costs this settlement has made.
     * @param MollieSettlement $settlement Settlement to calculate the costs of
     * @return Money Total costs
     */
    public static function determineTotalCost(MollieSettlement $settlement): Money
    {
        // Mollie uses FOUR decimals!
        $mollieContext = new CustomContext(4);
        $totalSum = money_value(0, $mollieContext);

        foreach ($settlement->periods as $yearKey => $yearPeriod) {
            foreach ($yearPeriod as $monthKey => $monthPeriod) {
                foreach ($monthPeriod->costs as $costKey => $costGroup) {
                    $totalSum = $totalSum->plus(money_value($costGroup->amountGross, $mollieContext));
                }
            }
        }

        return $totalSum->to(money_value(0)->getContext(), RoundingMode::HALF_UP);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public readonly string $settlementId
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MollieApiWrapper $api)
    {
        if ($priviligedApiKey = Config::get('gumbo.mollie.org_key')) {
            Log::info('Found priviliged API key, configuring Mollie to use that now.');
            $api->setApiKey($priviligedApiKey);
        } else {
            Log::warning('No priviliged API key was set, using the default one.');
        }

        try {
            $settlement = $api->settlements()->get($this->settlementId);
        } catch (ApiException $exception) {
            $this->fail(new RuntimeException("Settlement {$this->settlementId} failed to load: {$exception->getMessage()}", 400, $exception));
        }

        // Create or update the Settlement model on our end
        $settlementModel = Settlement::updateOrCreate([
            'mollie_id' => $this->settlementId,
        ], [
            'reference' => $settlement->reference,
            'status' => $settlement->status,

            // Money values
            'amount' => money_value($settlement->amount),
            'fees' => self::determineTotalCost($settlement),

            // Dates
            'created_at' => Date::parse($settlement->createdAt),
            'settled_at' => $settlement->settledAt ? Date::parse($settlement->settledAt) : null,
        ]);

        // Fetch the data from Mollie
        $molliePayments = Collection::make($settlement->payments());
        $mollieRefunds = Collection::make($settlement->refunds());

        // Find involved entities eagerly.
        $involvedPayments = Payment::query()
            ->where('provider', 'mollie')
            ->whereIn('transaction_id', Collection::make()
                ->merge($molliePayments->pluck('id'))
                ->merge($mollieRefunds->pluck('paymentId')))
            ->with('payable')
            ->get()
            ->keyBy('transaction_id');

        // Bind the payments and refunds
        $settlementModel->payments()->sync(
            $this->buildPaymentMap($molliePayments, $involvedPayments),
        );

        $settlementModel->refunds()->sync(
            $this->buildRefundMap($mollieRefunds, $involvedPayments),
        );

        // Find the Mollie objects that target a payment that we don't know about
        $settlementModel->missing_payments = $molliePayments->whereNotIn('id', $involvedPayments->keys())
            ->map(fn (MolliePayment $payment) => [
                'id' => $payment->id,
                'amount' => money_value($payment->amount),
                'settlementAmount' => money_value($payment->settlementAmount),
            ]);

        $settlementModel->missing_refunds = $mollieRefunds->whereNotIn('paymentId', $involvedPayments)
            ->map(fn (MollieRefund $refund) => [
                'id' => $refund->id,
                'payment_id' => $refund->paymentId,
                'amount' => money_value($refund->amount),
                'settlementAmount' => money_value($refund->settlementAmount),
            ]);

        $settlementModel->save();
    }

    /**
     * Filter the list of Mollie payments to those that also exist in the database (the involved payments)
     * and add the amount that was paid out (if it somehow occurs multiple times).
     * @param Collection<MolliePayment> $molliePayments
     * @param Collection<Payment> $involvedPayments
     * @return Collection<int,object>
     */
    private function buildPaymentMap(Collection $molliePayments, Collection $involvedPayments): Collection
    {
        $amountByPayment = $this->determineTotalValueByKey($molliePayments, 'id', 'settlementAmount');

        return $involvedPayments
            ->only($molliePayments->pluck('id')->all())
            ->mapWithKeys(fn (Payment $payment) => [$payment->id => [
                'amount' => $amountByPayment[$payment->transaction_id],
            ]]);
    }

    /**
     * Filter the list of Mollie efunds to those that also exist in the database (the involved payments)
     * and add the amount that was refunded.
     * @param Collection<MollieRefund> $mollieRefunds
     * @param Collection<Payment> $involvedPayments
     * @return Collection<int,object>
     */
    private function buildRefundMap(Collection $mollieRefunds, Collection $involvedPayments): Collection
    {
        $amountByPayment = $this->determineTotalValueByKey($mollieRefunds, 'paymentId', 'settlementAmount');

        return $involvedPayments
            ->only($mollieRefunds->pluck('paymentId')->all())
            ->mapWithKeys(fn (Payment $payment) => [$payment->id => [
                'amount' => $amountByPayment[$payment->transaction_id],
            ]]);
    }

    private function determineTotalValueByKey(iterable $objects, string $key, string $amountKey = 'amount'): array
    {
        $valueByKey = [];

        foreach ($objects as $object) {
            $objectKeyValue = $object->{$key};
            $valueByKey[$objectKeyValue] = Money::total(
                $valueByKey[$objectKeyValue] ?? money_value(0),
                money_value($object->{$amountKey}),
            );
        }

        return $valueByKey;
    }
}
