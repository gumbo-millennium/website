<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Bots\Models\TelegramObject;
use App\Models\User;

interface TelegramNotification
{
    public function toTelegramMessage(User $notifiable): TelegramObject;
}
