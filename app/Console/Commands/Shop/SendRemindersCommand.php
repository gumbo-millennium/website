<?php

declare(strict_types=1);

namespace App\Console\Commands\Shop;

use App\Models\ScheduledMail;
use App\Models\Shop\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class SendRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends reminders to everyone who has to pay between now and 8 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $applicable = Order::query()
            ->whereNull('paid_at')
            ->where('created_at', '<', Date::now()->subHours(6))
            ->whereBetween('expires_at', [Date::now()->addHour(), Date::now()->addHours(8)])
            ->with(['user'])
            ->cursor();

        foreach ($applicable as $order) {
            // Check it
            $this->line("Order <info>{$order->number}</> eligible for reminder.");

            // Check if sent
            $scheduled = ScheduledMail::findForModelMail($order, 'pre-expiry');
            if ($scheduled->is_sent) {
                $this->line("Order reminder for <info>{$order->number}</> already sent.");

                continue;
            }

            // Dispatch reminder

            // Send it now
            $scheduled->scheduled_for = now();
            $scheduled->save();
        }
    }
}
