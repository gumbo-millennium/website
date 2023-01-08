<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Helpers\Str;
use App\Jobs\SendActivityMessageJob;
use App\Models\ActivityMessage;
use App\Models\Ticket;
use App\Nova\Actions\Traits\BlocksCancelledActivityRuns;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Markdown as MarkdownField;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class SendActivityMail extends Action
{
    use BlocksCancelledActivityRuns;

    public function __construct()
    {
        $this
            // The 'confirmation' is the body
            ->confirmText(implode(PHP_EOL, [
                __('To send a message to the (pending) enrollments of this activity, please type your message below.'),
                __('Afterwards, you may customize the audience and schedule when the message is to be sent.'),
            ]))

            // The buttons
            ->confirmButtonText(__('Schedule / Send Message'))
            ->cancelButtonText(__('Cancel'))

            // And make sure it's not chainable
            ->onlyOnDetail();
    }

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('Send Message to Participants');
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() !== 1) {
            return $this->error(__('This action can only be performed on a single activity.'));
        }

        /** @var Activity $activity */
        $activity = $models->first();

        $subject = $fields->get('title');
        $body = $fields->get('body');

        $tickets = $fields->get('tickets');
        $includePending = $fields->get('include_pending');
        $scheduledAt = $fields->get('send_at');
        $scheduledAtDate = $scheduledAt ? Date::parse($scheduledAt) : null;

        // Ensure schedule date is valid
        if ($scheduledAtDate) {
            $minimumDate = Date::now()->startOfHour();
            $maximumDate = $activity->end_date->addDays(30)->startOfHour();
            $scheduledAtDate = $scheduledAtDate->max($minimumDate)->min($maximumDate);
        }

        $selectedTicketIds = collect($tickets)->filter()->keys();

        // Find all of the Activity's tickets that have been selected, if any.
        $ticketIds = Ticket::query()
            ->whereBelongsTo($activity)
            ->whereIn('id', $selectedTicketIds)
            ->pluck('id');

        $message = new ActivityMessage([
            'subject' => $subject,
            'body' => $body,
            'include_pending' => $includePending,
            'scheduled_at' => $scheduledAtDate,
        ]);

        // Associate activity and user
        $message->activity()->associate($activity);
        $message->sender()->associate(Auth::user());

        $message->save();

        // Associate tickets
        $message->tickets()->sync($ticketIds);
        $message->save();

        if ($message->scheduled_at === null) {
            SendActivityMessageJob::dispatch($message);

            return $this->message(__('The message will be sent shortly.'));
        }

        return $this->message(__('The message has been scheduled for :date.', [
            'date' => $message->scheduled_at->isoFormat('D MMM \'YY, HH:mm'),
        ]));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        /** @var Activity $activity */
        $activity = $request->findModelOrFail($request->resourceId ?? $request->resources);
        $tickets = $activity->tickets()->with('enrollments')->get();

        $ticketNames = $tickets->keyBy('id')->map(fn (Ticket $ticket) => __(':title (:price, :visibility)', [
            'title' => $ticket->title,
            'price' => Str::price($ticket->total_price) ?? __('Free'),
            'visibility' => $ticket->is_public ? __('Public') : __('Members Only'),
        ]));

        $maxSendDate = $activity->end_date->addDays(30)->startOfHour();

        return [
            // First off, the message itself
            Text::make(__('Mail Subject'), 'title')
                ->rules([
                    'required',
                    'string',
                    'between:5,70',
                ])
                ->help(__("The subject is all yours, but it's advised to include the activity name in the subject.")),

            MarkdownField::make(__('Mail Body'), 'body')
                ->rules([
                    'required',
                ])
                ->help(Str::markdown(__('Do **not** use pictures, they will be removed from the message.')))
                ->fullWidth(),

            // Then the audiences
            Fields\Heading::make(__('Audience')),
            Fields\BooleanGroup::make(__('Receiving Tickets'), 'tickets')
                ->options($ticketNames)
                ->help(__('Leave empty to include all current and future tickets.')),

            Fields\Boolean::make(__('Include Pending'), 'include_pending')
                ->help(__('If checked, pending enrollments will be included in the audience.')),

            // And then the schedule
            Fields\Heading::make(__('Scheduling'))
                ->help(implode(PHP_EOL, [
                    __('You may schedule the message to be sent at a later date.'),
                    __('The scheduled date must be before or at the end of the event.'),
                ])),

            Fields\DateTime::make(__('Send at'), 'send_at')
                ->help(implode(PHP_EOL, [
                    __('Leave empty to send the message immediately.'),
                    __('You may only schedule a message up to 30 days after the event has ended (:date).', [
                        'date' => $maxSendDate->isoFormat('D MMMM, HH:mm'),
                    ]),
                ]))
                ->min(Date::now()->startOfHour())
                ->max($maxSendDate)
                ->step(new CarbonInterval('PT5M')),
        ];
    }
}
