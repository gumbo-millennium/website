<?php

declare(strict_types=1);

namespace App\Console\Commands\Shop;

use App\Enums\PaymentStatus;
use App\Facades\Payments;
use App\Models\Shop\Order;
use App\Notifications\Shop\OrderExpired;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class CancelExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drops expired orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $applicable = Order::query()
            ->whereNull('paid_at')
            ->whereNull('cancelled_at')
            ->where('expires_at', '<', Date::now()->subHour())
            ->with(['user'])
            ->cursor();

        /** @var Order $order */
        foreach ($applicable as $order) {
            // Check it
            $this->line("Order <info>{$order->number}</> eligible for cancellation.");

            // Check online
            if ($order->status !== PaymentStatus::OPEN) {
                $this->line("Order <info>{$order->number}</> seems changed online.");
            }

            // Cancel it
            $order->cancelled_at = Date::now();
            $order->save();

            // Send notice
            if ($order->user) {
                $order->user->notifyNow(new OrderExpired($order));
            }
        }
    }
}
