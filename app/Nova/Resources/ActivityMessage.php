<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\ActivityMessage as ActivityMessageModel;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * Messages sent from activities to users.
 * @mixin ActivityMessageModel
 */
class ActivityMessage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = ActivityMessageModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'subject';

    /**
     * Name of the group.
     *
     * @var string
     */
    public static $group = 'Messages';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        $activityTickets = $this->activity?->tickets ?? collect();
        $checkedTickets = ($this->tickets->isEmpty() ? $activityTickets : $this->tickets)->mapWithKeys(fn ($ticket) => [$ticket->id => true]);

        return [
            Fields\ID::make(),

            // Add activity
            Fields\BelongsTo::make(__('Activity'), 'activity', Activity::class),

            Fields\Text::make(__('Sender'), fn () => $this->sender?->name),

            Fields\BooleanGroup::make(__('Receiving Tickets'), fn () => $checkedTickets)
                ->help(__('The checked tickets above will recieve this message.'))
                ->options($activityTickets->pluck('title', 'id'))
                ->onlyOnDetail(),

            Fields\Boolean::make(__('Send to Pending Enrollments'), 'include_pending'),

            Fields\HasMany::make(__('Recieving tickets'), 'tickets', Ticket::class)
                ->onlyOnForms(),

            Fields\Number::make(
                __('Number of Recipients'),
                'recipients',
                fn () => $this->sent_at === null
                ? __(':count (expected)', ['count' => $this->expected_recipients])
                : $this->recipients,
            ),

            new Panel(__('Message'), [
                Fields\Text::make(__('Mail Subject'), 'subject'),

                Fields\Markdown::make(__('Mail Body'), 'body')
                    ->alwaysShow(),
            ]),

            new Panel(__('Metadata'), [
                // Dates
                Fields\DateTime::make(__('Created at'), 'created_at')
                    ->onlyOnDetail(),

                Fields\DateTime::make(__('Scheduled for'), 'scheduled_at')
                    ->displayUsing(fn ($value) => $value ? $value : __('Immediately'))
                    ->onlyOnDetail(),

                Fields\DateTime::make(__('Sent at'), 'sent_at')
                    ->onlyOnDetail(),
            ]),
        ];
    }

    /**
     * Override index fields to show more consice information.
     */
    public function fieldsForIndex(NovaRequest $request): array
    {
        return [
            Fields\ID::make(),

            // Add activity
            Fields\BelongsTo::make(__('Activity'), 'activity', Activity::class),

            Fields\Text::make(__('Sender'), fn () => $this->sender?->name),

            Fields\Text::make(__('Mail title'), 'subject'),

            Fields\Markdown::make(__('Mail body'), 'body'),

            Fields\Stack::make(__('Status'), 'sent_at', [
                Fields\Line::make('Sent at', function () {
                    if ($this->sent_at) {
                        return __('Sent :date', ['date' => $this->sent_at->isoFormat('D MMM \'YY, HH:mm')]);
                    }

                    if ($this->scheduled_at) {
                        return __('Scheduled for :date', ['date' => $this->scheduled_at->isoFormat('D MMM \'YY, HH:mm')]);
                    }

                    return __('Will be sent shortly');
                }),
                Fields\Line::make(
                    'Recipient count',
                    fn () => $this->sent_at
                        ? __(':count recipient(s)', ['count' => $this->recipients])
                        : __(':count expected recipient(s)', ['count' => $this->expected_recipients]),
                )->asSmall(),
            ]),
        ];
    }
}
