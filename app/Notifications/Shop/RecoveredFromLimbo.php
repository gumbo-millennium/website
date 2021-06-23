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

class RecoveredFromLimbo extends ShopNotification implements TelegramNotification
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
        $orderDate = $this->order->created_at->isoFormat('dddd DD MMMM');

        return (new MailMessage())
            ->subject('je kunt nu betalen!')

            ->greeting("Beste {$notifiable->first_name},")

            ->line('Sorry dat er wat fout ging bij je bestelling, waardoor je niet verder kon.')
            ->line("We hebben je bestelling van {$orderDate} opgeduikeld uit limbo en je kan hem nu betalen.")

            ->action('Bekijk bestelling', route('shop.order.show', $this->order))

            ->line('Als je geen behoefte meer hebt om deze bestelling af te ronden, dan hoef je niks te doen.')

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
        Je bestelling is hersteld 💚

        Sorry voor de overlast, maar je kan nu je bestelling betalen.
        De bestelling verloopt over {$expiresIn}, dus voor die tijd betalen of we gooien 'm weg.
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
