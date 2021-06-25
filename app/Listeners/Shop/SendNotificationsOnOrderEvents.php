<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderCancelledEvent;
use App\Events\Shop\OrderEvent;
use App\Events\Shop\OrderExpiredEvent;
use App\Events\Shop\OrderPaidEvent;
use App\Events\Shop\OrderShippedEvent;
use App\Notifications\Shop\OrderCancelled;
use App\Notifications\Shop\OrderExpired;
use App\Notifications\Shop\OrderPaid;
use App\Notifications\Shop\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNotificationsOnOrderEvents implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(OrderEvent $event)
    {
        // Get order
        $order = $event->getOrder();
        $user = $order->user;

        if (! $user) {
            return;
        }

        if ($event instanceof OrderPaidEvent) {
            $user->notify(new OrderPaid($order));

            return;
        }

        if ($event instanceof OrderShippedEvent) {
            $user->notify(new OrderShipped($order));

            return;
        }

        if ($event instanceof OrderExpiredEvent) {
            $user->notify(new OrderExpired($order));

            return;
        }

        if ($event instanceof OrderCancelledEvent) {
            $user->notify(new OrderCancelled($order));

            return;
        }
    }
}
