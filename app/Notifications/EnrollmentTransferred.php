<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Paid;
use App\Models\User;
use App\Notifications\Traits\UsesStripePaymentData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Action;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class EnrollmentTransferred extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use UsesStripePaymentData;

    protected Enrollment $enrollment;
    protected User $oldUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Enrollment $enrollment, User $old)
    {
        $this->enrollment = $enrollment->withoutRelations();
        $this->oldUser = $old->withoutRelations();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Reload enrollment
        $enrollment = $this->enrollment
            ->refresh()
            ->loadMissing(['user', 'activity', 'activity.role:id,title']);


        // Get some shorthands
        $activity = $enrollment->activity;

        // Send mail
        $action = new Action('Bekijk activiteit', route('activity.show', compact('activity')));

        if ($notifiable->is($enrollment->user)) {
            return $this->formatForRecipient($notifiable, $activity, $enrollment, $action);
        }

        return $this->formatForSender($notifiable, $activity, $enrollment, $action);
    }

    private function formatForRecipient(
        User $recipient,
        Activity $activity,
        Enrollment $enrollment,
        Action $action
    ): MailMessage {
        // Variables
        $oldUser = $this->oldUser;

        // Begin
        $mail = new MailMessage();

        // Assign basics
        $mail->subject("Je hebt een inschrijving voor {$activity->name} overgenomen")
            ->greeting("Je bent nu ingeschreven voor {$activity->name}")
            ->line("Beste {$recipient->first_name},")
            ->line(<<<TEXT
            *{$oldUser->name}* heeft de inschrijving voor *{$activity->name}* overgedragen aan jou. Dit betekend
            dat jij nu ingeschreven staat voor deze activiteit, met alle rechten en plichten die hierbij horen.
            TEXT)
            ->line("Beter loop je even naar {$oldUser->name} toe met je liefste blik als bedankje 🥺.");

        if ($enrollment->state instanceof Paid) {
            $mail->line(<<<MARKDOWN
            Het wordt *nóg* beter, aangezien {$oldUser->first_name} al heeft betaald voor deze inschrijving.
            Je mag lekker zelf regelen hoe dat geld terugkomt bij {$oldUser->first_name}.
            MARKDOWN);
        } elseif (!$enrollment->state->isStable() && $enrollment->expire) {
            $exprireDate = $enrollment->expire->isoFormat('D MMM Y, HH:mm (z)');
            $mail->line(<<<MARKDOWN
            **Let op**: deze inschrijving is niet afgerond. Dit betekend dat je tot {$exprireDate} hebt
            om hem af te ronden, anders wordt hij geannuleerd en is alle moeite van {$oldUser->name} voor
            niets geweest.
            MARKDOWN);
        }

        $mail->line('Voor meer info, kan je via onderstaande knop naar de activiteit.')
            ->line($action);

        return $mail;
    }

    private function formatForSender(
        User $recipient,
        Activity $activity,
        Enrollment $enrollment,
        Action $action
    ): MailMessage {
        // Variables
        $newUser = $enrollment->user;

        // Begin
        $mail = new MailMessage();

        // Assign basics
        $mail->subject("Overdracht van inschrijving voor {$activity->name}")
            ->greeting("Je inschrijving is nu van {$newUser->first_name}")
            ->line("Beste {$recipient->first_name},")
            ->line("Je inschrijving voor *{$activity->name}* is succesvol overgedragen naar *{$newUser->name}*.")
            ->line("{$newUser->first_name} is hier vast super blij mee.");

        if ($enrollment->state instanceof Paid) {
            $mail->line(<<<MARKDOWN
            **Let op**: Omdat je al hebt betaald, is het aan jou om te zorgen dat jij het geld van
            {$newUser->first_name} weet te plunderen.
            MARKDOWN);
        }

        $mail->line(<<<MARKDOWN
            Wil je nog wat nostaligsche gevoelens over de activiteit waar je {$newUser->first_name} nu naartoe stuurt 🥺?
            MARKDOWN)
            ->line($action);

        return $mail;
    }
}
