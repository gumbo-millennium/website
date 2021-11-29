<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\Shop\Order;
use App\Services\Payments\MolliePaymentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;

class MigrateOrdersToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var Order $order */
        foreach (Order::withoutGlobalScopes()->cursor() as $order) {
            if (! $order->payment_id) {
                continue;
            }

            $mollieMeta = [
                'provider' => MolliePaymentService::getName(),
                'transaction_id' => $order->payment_id,
            ];

            Payment::where($mollieMeta)->forceDelete();

            $payment = $order->payments()->make(array_merge($mollieMeta, [
                'price' => $order->price,
            ]));

            $payment->user()->associate($order->user);

            $payment->forceFill([
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
                'paid_at' => $order->paid_at,
                'expired_at' => $order->expired_at,
                'cancelled_at' => $order->cancelled_at,
            ]);

            $payment->save();
        }
    }
}
