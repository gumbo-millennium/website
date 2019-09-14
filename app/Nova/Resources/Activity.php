<?php

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Nova\Fields\Price;
use App\Nova\Fields\Seats;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * An activity resource
 */
class Activity extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\Models\\Activity';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * @inheritDoc
     */
    public static $defaultSort = 'event_start';


    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'description',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Activities');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Activity');
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        $startDate = optional($this->event_start)->format('d-m-Y');
        $endDate = optional($this->event_end)->format('d-m-Y');
        $startTime = optional($this->event_start)->format('H:i');
        $endTime = optional($this->event_end)->format('H:i');

        if ($startDate !== $endDate) {
            return sprintf('%s – %s', $startDate, $endDate);
        }

        return sprintf('%s (%s tot %s)', $startDate, $startTime, $endTime);
    }


    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            new Panel(__('activities.panels.base'), $this->mainFields()),
            new Panel(__('activities.panels.date-price'), $this->pricingFields()),
            new Panel(__('activities.panels.enrollments'), $this->enrollmentFields()),

            HasMany::make(__('Enrollments'), 'enrollments', Enrollment::class),
        ];
    }

    public function mainFields() : array
    {
        return [
            ID::make()->sortable(),

            TextWithSlug::make(__('Title'), 'name')
                ->sortable()
                ->slug('slug')
                ->rules('required', 'between:4,255'),

            Slug::make(__('Slug'), 'slug')
                ->help('URL, moet uniek zijn'),

            Text::make(__('Tagline'), 'tagline')
                ->hideFromIndex()
                ->rules('nullable', 'string', 'between:4,255'),

            NovaEditorJs::make(__('Description'), 'description')
                ->rules('required'),

            DateTime::make(__('Created At'), 'created_at')
                ->readonly()
                ->onlyOnDetail(),

            DateTime::make(__('Updated At'), 'updated_at')
                ->readonly()
                ->onlyOnDetail(),

            // BelongsTo::make('Role', 'role', Role::class),
        ];
    }

    public function pricingFields() : array
    {
        return [
            DateTime::make(__('Event Start'), 'event_start')
                ->sortable()
                ->rules('required', 'date')
                ->firstDayOfWeek(1),

            DateTime::make(__('Event End'), 'event_end')
                ->rules('required', 'date', 'after:event_start')
                ->hideFromIndex()
                ->firstDayOfWeek(1),

            Price::make(__('Member Price'), 'price_member')
                ->min(1)
                ->max(200)
                ->step(0.05)
                ->nullable()
                ->nullValues([''])
                ->rules('nullable', 'numeric', 'min:2.50')
                ->help('In <strong>centen</strong>, exclusief administratiekosten'),

            Price::make(__('Guest Price'), 'price_guest')
                ->min(1)
                ->max(200)
                ->step(0.05)
                ->nullable()
                ->nullValues([''])
                ->rules('nullable', 'numeric', 'min:2.50', 'gte:price_member')
                ->help('Excluding transaction fees'),
        ];
    }

    public function enrollmentFields() : array
    {
        return [
            DateTime::make(__('Enrollment Start'), 'enrollment_start')
                ->rules('nullable', 'date', 'before:event_end')
                ->hideFromIndex()
                ->nullable()
                ->firstDayOfWeek(1),

            DateTime::make(__('Enrollment End'), 'enrollment_end')
                ->rules('nullable', 'date', 'before_or_equal:event_end')
                ->hideFromIndex()
                ->nullable()
                ->firstDayOfWeek(1),

            Text::make(__('Enrollment status'), function () {
                $label = $this->enrollment_status ? 'open' : 'closed';
                return ucfirst(__("activities.enrollment.{$label}"));
            })->onlyOnIndex(),

            Seats::make(__('Total Seats'), 'seats')
                ->min(1)
                ->step(1)
                ->nullable()
                ->nullValues(['', '0'])
                ->rules('nullable', 'numeric', 'min:1'),

            Seats::make(__('Guest Seats'), 'public_seats')
                ->min(1)
                ->step(1)
                ->nullable()
                ->nullValues([''])
                ->rules('nullable', 'numeric', 'lt:seats')
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
