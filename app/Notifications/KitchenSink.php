<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as LaravelVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class KitchenSink extends LaravelVerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Otherwise, they're updating
        return (new MailMessage())
            ->markdown('mail::kitchen-sink', [
                'user' => $notifiable
            ]);
    }
}
