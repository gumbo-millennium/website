<?php

declare(strict_types=1);

namespace App\Console\Commands\Shop;

use App\Facades\Payments;
use App\Models\Shop\Order;
use App\Notifications\Shop\RecoveredFromLimbo;
use Illuminate\Console\Command;

class RepairOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:repair-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds orders that are unpaid and not linked to a Mollie ID';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $orders = Order::query()
            ->whereNull('paid_at')
            ->whereNull('payment_id')
            ->cursor();

        foreach ($orders as $order) {
            assert($order instanceof Order);

            $mollieOrder = Payments::createOrder($order);
            $order->payment_id = $mollieOrder->id;
            $order->save();

            $this->line(sprintf(
                'Created Mollie order <info>%s</> for <comment>%s</>.',
                $mollieOrder->id,
                $order->number,
            ));

            // Send message
            $order->user->notifyNow(new RecoveredFromLimbo($order));
        }

        return 0;
    }
}
