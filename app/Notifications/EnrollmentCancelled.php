<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Helpers\Str;
use App\Models\Enrollment;
use App\Notifications\Traits\UsesStripePaymentData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class EnrollmentCancelled extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use UsesStripePaymentData;

    protected Enrollment $enrollment;

    /**
     * Create a new notification instance.
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment->withoutRelations();
    }

    /**
     * Get the notification's delivery channels.
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function toMail()
    {
        // Reload enrollment
        $enrollment = $this->enrollment
            ->refresh()
            ->loadMissing(['user', 'activity', 'activity.role:id,title']);


        // Get some shorthands
        $user = $enrollment->user;
        $activity = $enrollment->activity;
        $price = Str::price($enrollment->total_price);

        // Get payment data
        $paymentData = $this->getPaymentInfo($enrollment);

        // Send mail
        $mail = (new MailMessage())
            ->subject("Uitgeschreven voor {$activity->name}")
            ->greeting('Je doet niet meer mee!')
            ->line("Beste {$user->first_name},")
            ->line("Je inschrijving voor {$activity->name} is geannuleerd.");

        if ($enrollment->deleted_reason === 'timeout') {
            $expire = $enrollment->expire ?? now();
            $expireText = $expire->isoFormat('DDD MMMM Y, H:i');
            $mail
                ->line(<<<TEXT
                Om plek vrij te houden voor andere deelnemers, is er een
                deadline waarvoor je je inschrijving moet afronden. Voor
                jouw inschrijving was dit {$expireText}. Je inschrijving
                is hierom geannuleerd.
                TEXT);
        }

        if ($paymentData['paid']) {
            if ($paymentData['iban']) {
                $mail->line(<<<TEXT
                Het betaalde bedrag van {$price} zal binnen enkele werkdagen
                teruggeboekt worden op je {$paymentData['bank']} rekening eindigend
                op {$paymentData['iban']}.
                TEXT);
            } else {
                $mail->line(<<<TEXT
                Het betaalde bedrag van {$price} zal binnen enkele werkdagen
                teruggeboekt worden.
                TEXT);
            }
        }

        // Add action button
        $mail
            ->action('Bekijk activiteit', route('activity.show', compact('activity')))
            ->line('Indien je tóch weer mee wil doen, klik dan op bovenstaande knop om je opnieuw aan te melden.');

        // Return mail
        return $mail;
    }
}
