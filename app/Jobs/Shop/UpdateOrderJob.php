<?php

declare(strict_types=1);

namespace App\Jobs\Shop;

use App\Events\Shop\OrderCancelledEvent;
use App\Events\Shop\OrderExpiredEvent;
use App\Events\Shop\OrderPaidEvent;
use App\Events\Shop\OrderShippedEvent;
use App\Facades\Payments;
use DateTimeImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class UpdateOrderJob extends ShopJob
{
    protected static function fromApiTime(string $time): DateTimeImmutable
    {
        return Date::parse($time, 'UTC')
            ->setTimezone(Config::get('app.timezone'))
            ->toImmutable();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = $this->order;

        // No order
        if (! $mollieOrder = Payments::findOrder($order)) {
            Log::warn('Failed to find Mollie order for {order}', [
                'order' => $order,
            ]);

            return;
        }

        // Prep info for log
        $logInfo = [
            'order' => $order,
            'mollie-order' => $mollieOrder,
        ];
        Log::debug('Retireved order {order} from Mollie', $logInfo);

        if ($mollieOrder->isPaid() && $order->paid_at === null) {
            $order->paid_at = self::fromApiTime($mollieOrder->paidAt);
            $order->save();

            Log::info('Marked order {order} as paid', $logInfo);

            // Fire event
            OrderPaidEvent::dispatch($order);
        }

        // Check if expired
        if ($mollieOrder->isExpired() && $order->cancelled_at === null) {
            $order->cancelled_at = self::fromApiTime($mollieOrder->expiredAt);
            $order->save();

            Log::info('Marked order {order} as expired', $logInfo);

            // Fire event
            OrderExpiredEvent::dispatch($order);
        }

        // Check if cancelled
        if ($mollieOrder->isCanceled() && $order->cancelled_at === null) {
            $order->cancelled_at = self::fromApiTime($mollieOrder->canceledAt);
            $order->save();

            Log::info('Marked order {order} as cancelled', $logInfo);

            // Fire event
            OrderCancelledEvent::dispatch($order);
        }

        // Check if shipped
        if ($order->cancelled_at === null && $shipment = Payments::getShipment($order)) {
            $order->shipped_at = self::fromApiTime($shipment->createdAt);
            $order->save();

            Log::info('Marked order {order} as shipped', $logInfo);

            // Fire event
            OrderShippedEvent::dispatch($order);
        }
    }
}
