<?php

declare(strict_types=1);

namespace App\Channels;

use App\Contracts\TelegramNotification;
use App\Contracts\TelegramNotificationWithAfter;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramChannel
{
    /**
     * Send the given notification.
     *
     * @return void
     */
    public function send($notifiable, TelegramNotification $notification)
    {
        // Only users can be sent notifications
        if (! $notifiable instanceof User || ! $notifiable->telegram_id) {
            return;
        }

        // Allow message to construct
        $message = $notification->toTelegramMessage($notifiable);

        // Enforce some rules
        $message->chatId($notifiable->telegram_id);
        $message->disableWebPagePreview(true);

        // Get the bot out
        $bot = Telegram::bot();

        // Determine proper action
        $method = 'send' . class_basename($message);
        if (! method_exists($bot, $method)) {
            $method = 'sendMessage';
        }

        // Send and check result
        try {
            $result = $bot->{$method}($message->toArray());
        } catch (TelegramResponseException $exception) {
            Log::warning('Failed to send message to Telegram: {exception}', [
                'exception' => $exception,
                'message' => $message->toArray(),
            ]);

            return;
        }

        // Allow an after hook
        if ($notification instanceof TelegramNotificationWithAfter) {
            $notification->afterTelegramMessage($result, $notifiable);
        }
    }
}
