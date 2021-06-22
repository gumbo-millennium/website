<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Telegram\Bot\Objects\Message;

interface TelegramNotificationWithAfter extends TelegramNotification
{
    public function afterTelegramMessage(Message $sentMessage, User $notifiable): void;
}
