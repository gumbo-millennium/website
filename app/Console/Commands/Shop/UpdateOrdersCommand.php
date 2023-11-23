<?php

declare(strict_types=1);

namespace App\Console\Commands\Shop;

use App\Facades\Payments;
use App\Jobs\Shop\UpdateOrderJob;
use App\Models\Shop\Order;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

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
    protected $description = 'Updates orders that might have updates.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $orders = Order::query()
            ->whereNull('shipped_at')
            ->whereNull('cancelled_at')
            ->whereNotNull('payment_id')
            ->cursor();

        foreach ($orders as $order) {
            assert($order instanceof Order);

            // Get order
            if (! Payments::findOrder($order)) {
                $this->line("Skipped <info>{$order->number}</>, no associated order");

                continue;
            }

            $this->line("Processing <info>{$order->number}</>...", null, OutputInterface::VERBOSITY_VERBOSE);

            UpdateOrderJob::dispatchSync($order);

            $this->line("Processed <info>{$order->number}</>.");
        }

        return 0;
    }
}
