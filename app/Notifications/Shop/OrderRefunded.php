<?php

declare(strict_types=1);

namespace App\Notifications\Shop;

use App\Bots\Models\Message;
use App\Bots\Models\TelegramObject;
use App\Channels\TelegramChannel;
use App\Contracts\TelegramNotification;
use App\Helpers\Str;
use App\Models\Shop\Order;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Telegram\Bot\Keyboard\Keyboard;

class OrderRefunded extends ShopNotification implements TelegramNotification
{
    protected int $amount;

    protected string $account;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, int $amount, string $account)
    {
        parent::__construct($order);

        $this->amount = $amount;
        $this->account = $account;
    }

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
        $amount = Str::price($this->amount);

        return (new MailMessage())
            ->subject("Terugbetaling voor bestelling {$order->number} verzonden")

            ->greeting("Beste {$notifiable->first_name},")

            ->line("Er is een terugbetaling verstuurd naar Mollie voor bestelling {$order->number}.")
            ->line("Binnen enkele dagen krijg je het bedrag van {$amount} teruggestort op je bankrekening eindigend op {$this->account}.")

            ->action(
                'Bekijk bestelling',
                route('shop.order.show', $order),
            )

            ->salutation('Tot snel!');
    }

    /**
     * Get the Telegram message to send.
     */
    public function toTelegramMessage(User $notifiable): TelegramObject
    {
        $order = $this->order;
        $amount = Str::price($this->amount);

        $message = <<<TEXT
            💸 Er is een terugbetaling naar je onderweg.

            Je krijgt binnen een paar dagen {$amount} terug voor het annuleren van {$order->number}.
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
