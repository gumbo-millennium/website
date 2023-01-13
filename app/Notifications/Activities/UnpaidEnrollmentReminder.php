<?php

declare(strict_types=1);

namespace App\Notifications\Activities;

use App\Helpers\Str;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class UnpaidEnrollmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    private Enrollment $enrollment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $enrollment = $this->enrollment;
        $user = $enrollment->user;
        $activity = $enrollment->activity;

        $message = (new MailMessage())
            ->subject(__('Payment reminder for :activity', ['activity' => $activity->name]))
            ->greeting(__('Hello :name', ['name' => $user->first_name]))
            ->line(__("You've been enrolled into :activity since :date, and while your enrollment has been confirmed, it's still unpaid.", [
                'activity' => $activity->name,
                'date' => $enrollment->created_at->isoFormat('D MMMM YYYY'),
            ]))
            ->line(__('Please pay your enrollment fee of :amount before the activity starts on :date.', [
                'amount' => Str::price($enrollment->total_price),
                'date' => $activity->start_date->isoFormat('D MMMM YYYY, HH:mm'),
            ]))
            ->action(__('Pay enrollment'), URL::route('enroll.show', $activity));

        if ($activity->start_date->diffInDays() < 7) {
            $message->line(__("This has been your final reminder. If you don't pay your enrollment fee, access to the activity may be denied."));
        }

        $message->salutation(sprintf("%s\n%s", __('With robot love,'), Config::get('app.name')));

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
            'enrollment_id' => $this->enrollment->id,
            'user' => $this->enrollment->user->name,
            'activity' => $this->enrollment->activity->name,
            'price' => $this->enrollment->total_price,
        ];
    }
}
