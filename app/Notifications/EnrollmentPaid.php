<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class EnrollmentPaid extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected Enrollment $enrollment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment->withoutRelations();
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
     * @return \Illuminate\Notifications\Messages\MailMessage
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

        // Send mail
        $mail = (new MailMessage())
            ->subject("Betaalbevestiging voor {$activity->name} 🎉")
            ->greeting('Bedankt voor je betaling!')
            ->line("Beste {$user->first_name},")
            ->line("Je betaling voor {$activity->name} is in goede orde ontvangen.")
            ->line('Je inschrijving is nu definitief.')
            ->action('Bekijk activiteit', route('activity.show', compact('activity')));

        // Finally get some closure
        $tail = 'hopelijk tot de volgende!';

        // Add thank you from the group if present
        $group = optional($activity->role)->title;
        if ($group) {
            $tail = "De {$group} is je erg dankbaar :)";
        }

        // Send the mail with the final goodbye
        return $mail
            ->line("Bedankt voor het gebruiken van de Gumbo Millennium website, {$tail}.");
    }
}
