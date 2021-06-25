<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Mail\Shop\NewOrderBoardMail;
use App\Mail\Shop\NewOrderUserMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendMailsOnOrderPaid implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(OrderPaidEvent $event)
    {
        // Get order
        $order = $event->getOrder();

        // Send mails
        Mail::to($order->user)->send(new NewOrderUserMail($order));
        Mail::to(Config::get('gumbo.mail-recipients.board'))->send(new NewOrderBoardMail($order));
    }
}
