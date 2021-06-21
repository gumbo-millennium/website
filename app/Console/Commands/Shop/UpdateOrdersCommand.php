<?php

declare(strict_types=1);

namespace App\Console\Commands\Shop;

use App\Facades\Payments;
use App\Models\Shop\Order;
use Illuminate\Console\Command;

class UpdateOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:update-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls in information about orders that have not been paid yet.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $orders = Order::query()
            ->whereNull('paid_at')
            ->whereNotNull('payment_id')
            ->cursor();

        foreach ($orders as $order) {
            assert($order instanceof Order);

            $paidAt = Payments::paidAt($order);

            if ($paidAt === null) {
                continue;
            }

            $order->paid_at = $paidAt;
            $order->save();

            $this->line(sprintf(
                'Marked order <info>%s</> as paid.',
                $order->number,
            ));
        }

        return 0;
    }
}
