<?php

declare(strict_types=1);

namespace App\Notifications\Shop;

use App\Bots\Models\Message;
use App\Bots\Models\TelegramObject;
use App\Channels\TelegramChannel;
use App\Contracts\TelegramNotification;
use App\Helpers\Str;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Date;
use Telegram\Bot\Keyboard\Keyboard;

class ExpiresSoonReminder extends ShopNotification implements TelegramNotification
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
            TelegramChannel::class,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $order = $this->order;
        $value = Str::price($this->order->price);

        $expiration = $order->expires_at->isoFormat('dddd D MMMM, \o\m HH:mm');
        $orderDate = $this->order->created_at->isoFormat('dddd D MMMM');
        $orderTime = $this->order->created_at->isoFormat('HH:mm');

        return (new MailMessage())
            ->subject('Je webwinkel bestelling verloopt bijna!')
            ->greeting("Beste {$notifiable->first_name},")

            ->line("Op {$orderDate} om {$orderTime} heb je een bestelling geplaatst in de Gumbo webwinkel ter waarde van {$value}.")
            ->line("Deze bestelling moet je betaald hebben voor {$expiration}, anders annuleren we deze.")

            ->action('Nu betalen', url('/'))

            ->line('Als je geen behoefte hebt om deze bestelling af te ronden, dan hoef je niks te doen.')

            ->salutation('Tot snel!');
    }

    /**
     * Get the Telegram message to send.
     */
    public function toTelegramMessage(User $notifiable): TelegramObject
    {
        $order = $this->order;
        $expiresIn = Date::now()->diffForHumans($order->expires_at, CarbonInterface::DIFF_ABSOLUTE);
        $value = Str::price($this->order->price);

        $message = <<<TEXT
        ⏰ Je bestelling verloopt bijna!

        Je hebt nog {$expiresIn} om je Gumbo webwinkel bestelling {$order->number} af te ronden!
        TEXT;

        return Message::make($message)
            ->addKeyboardRow(
                Keyboard::inlineButton([
                    'text' => "Nu {$value} betalen",
                    'url' => route('shop.order.pay', $this->order),
                ]),
            )
            ->addKeyboardRow(
                Keyboard::inlineButton([
                    'text' => 'Bestelling annuleren',
                    'url' => route('shop.order.cancel', $this->order),
                ]),
            );
    }
}
