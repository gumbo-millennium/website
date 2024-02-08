<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail as LaravelVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends LaravelVerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        // Accounts newer than 15 mins are probably new
        if ($notifiable->wasRecentlyCreated || $notifiable->created_at > now()->subMinute(15)) {
            return $this->toNewMail($notifiable, $verificationUrl);
        }

        // Otherwise, they're updating
        return $this->toUpdatedEmail($notifiable, $verificationUrl);
    }

    /**
     * User is creating a new account.
     */
    private function toNewMail(User $user, string $url): MailMessage
    {
        return (new MailMessage())
            ->subject('Welkom bij Gumbo Millennium')
            ->greeting('Welkom bij Gumbo Millennium')
            ->line("Beste {$user->first_name},")
            ->line(<<<'TEXT'
                    Bedankt voor je aanmelding bij Gumbo Millennium. Om je
                    inschrijving af te ronden, hoef je alleen maar je
                    e-mailadres te bevestigen door op onderstaande knop te
                    drukken.
                TEXT)
            ->action('Bevestig je e-mailadres', $url)
            ->line(<<<'TEXT'
                    Het kan, na het bevestigen van je e-mailadres, even duren
                    voordat je toegang hebt. We halen de lidmaatschappen pas op
                    na verificatie van je e-mailadres.
                TEXT)
            ->line(<<<'TEXT'
                    Heb je geen account gemaakt bij Gumbo Millennium, dan hoef je
                    niks te doen.
                TEXT);
    }

    /**
     * User has changed e-mailadres.
     */
    private function toUpdatedEmail(User $user, string $url): MailMessage
    {
        return (new MailMessage())
            ->subject('Bevestig je e-mailadres')
            ->greeting('Bevestig je wijziging')
            ->line("Beste {$user->first_name},")
            ->line(<<<'TEXT'
                    Je e-mailadres geeft je toegang tot allerlei onderdelen van de
                    site, en als je lid bent krijg je automatisch toegang tot
                    bestanden zoals het documentensysteem en besloten activiteiten.
                TEXT)
            ->line(<<<'TEXT'
                    Om zeker te zijn dat de aangemaakte account ook écht bij dit
                    e-mailadres hoort, moet je 'm dus wel even bevestigen.
                TEXT)
            ->action('Bevestig je e-mailadres', $url)
            ->line(<<<'TEXT'
                    Zolang je e-mailadres niet bevestigd is, kan je je niet
                    inschrijven voor activiteiten.
                TEXT);
    }
}
