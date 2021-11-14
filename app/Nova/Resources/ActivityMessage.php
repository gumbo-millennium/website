<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\ActivityMessage as ActivityMessageModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;

/**
 * Messages sent from activities to users.
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
    public static $group = 'Berichten';

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
    public function fields(Request $request)
    {
        return [
            Fields\ID::make(),

            // Add activity
            Fields\BelongsTo::make(__('Activity'), 'activity', Activity::class),

            Fields\Text::make(__('Sender'), fn () => optional($this->sender)->name),

            // Add data
            Fields\Text::make(__('Target audience'), 'target_audience')
                ->displayUsing(static fn ($value) => __("gumbo.target-audiences.{$value}")),

            Fields\Text::make(__('Mail title'), 'subject'),

            Fields\Markdown::make(__('Mail body'), 'body'),

            // Dates
            Fields\DateTime::make(__('Created at'), 'created_at')
                ->onlyOnDetail(),

            Fields\DateTime::make(__('Sent at'), 'sent_at')
                ->onlyOnDetail(),

            Fields\Number::make(__('Number of recipients'), 'receipients')
                ->onlyOnDetail(),

            Fields\Text::make(__('Status'), fn () => $this->sent_at
                ? __('Sent to :count recipient(s)', ['count' => $this->receipients])
                : __('Not yet sent'), )
                ->onlyOnIndex(),
        ];
    }
}
