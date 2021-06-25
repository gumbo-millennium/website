<?php

declare(strict_types=1);

namespace App\Notifications\Shop;

use App\Bots\Models\TelegramObject;
use App\Channels\TelegramChannel;
use App\Contracts\TelegramNotification;
use App\Models\User;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Message;

class OrderPaid extends ShopNotification implements TelegramNotification
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [
            TelegramChannel::class,
        ];
    }

    public function toTelegramMessage(User $notifiable): TelegramObject
    {
        $order = $this->order;

        $message = <<<TEXT
        **💸 Bestelling {$order->number} is betaald**

        Het bestuur neemt contact met je op over de levering.
        TEXT;

        return Message::make($message)
            ->addKeyboardRow(
                Keyboard::inlineButton([
                    'text' => 'Bekijk bestelling',
                    'url' => route('shop.order.show', $this->order),
                ]),
            );
    }
}
