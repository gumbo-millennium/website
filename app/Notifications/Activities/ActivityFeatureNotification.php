<?php

declare(strict_types=1);

namespace App\Notifications\Activities;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class ActivityFeatureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Activity $activity;

    protected string $feature;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Activity $activity, string $feature)
    {
        $this->activity = $activity;

        $this->feature = $feature;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param \App\Models\User $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = $this->getSubject($notifiable);
        $body = $this->getBody($notifiable);
        $button = $this->getButton($notifiable);

        $bodyParts = explode("\n\n", $body);
        $greeting = $bodyParts[0];

        $message = (new MailMessage())
            ->subject($subject)
            ->greeting($greeting);

        foreach ($bodyParts as $line) {
            $message->line(trim($line));
        }

        $message
            ->action($button['text'], $button['url']);

        $message
            ->line("Als je geen behoefte meer hebt om naar deze activiteit te gaan, kan je je [hier]({$this->getLink()}) uitschijven.")
            ->salutation('Tot snel!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    private function replacePlaceholders($notifiable, string $body): string
    {
        $replaceMap = [
            '{user}' => $notifiable->first_name ?? $notifiable->name ?? $notifiable->alias ?? 'ontvanger',
            '{name}' => $this->activity->name,
            '{date}' => $this->activity->start_date->isoFormat('dddd D MMMMM'),
            '{link}' => $this->getLink(),
        ];

        return str_replace(
            array_keys($replaceMap),
            array_values($replaceMap),
            $body,
        );
    }

    /**
     * Returns notification subject.
     *
     * @param \App\Models\User|mixed $notifiable
     * @return null|string Null in case the body is empty
     */
    private function getSubject($notifiable): string
    {
        $title = Config::get("gumbo.activity-features.{$this->feature}.mail.subject", 'Informatie over {name}');

        return $this->replacePlaceholders($notifiable, $title);
    }

    /**
     * Returns notification body.
     *
     * @param \App\Models\User|mixed $notifiable
     * @return null|string Null in case the body is empty
     */
    private function getBody($notifiable): ?string
    {
        $body = Config::get("gumbo.activity-features.{$this->feature}.mail.body");

        return $body ? $this->replacePlaceholders($notifiable, $body) : null;
    }

    /**
     * Returns notification primary call-to-action.
     *
     * @param \App\Models\User|mixed $notifiable
     * @return string[]
     */
    private function getButton($notifiable): array
    {
        return [
            'text' => $this->replacePlaceholders($notifiable, Config::get(
                "gumbo.activity-features.{$this->feature}.mail.button.text",
                'Bekijk {name}',
            )),
            'url' => Config::get(
                "gumbo.activity-features.{$this->feature}.mail.button.url",
                $this->getLink(),
            ),
        ];
    }

    private function getLink(): string
    {
        return URL::route('activity.show', $this->activity);
    }
}
