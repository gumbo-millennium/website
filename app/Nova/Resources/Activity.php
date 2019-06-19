<?php

namespace App\Nova\Resources;

use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;

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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            new Panel(__('Base information'), $this->mainFields()),
            new Panel(__('Date and Cost'), $this->pricingFields()),
            new Panel(__('Enrollments'), $this->enrollmentFields()),

            HasMany::make('Enrollments'),
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

            Textarea::make(__('Description'), 'description')
                ->rules('required', 'min:10'),

            DateTime::make(__('Created At'), 'created_at')
                ->readonly()
                ->exceptOnForms(),

            DateTime::make(__('Updated At'), 'updated_at')
                ->readonly()
                ->exceptOnForms(),

            // BelongsTo::make('Role', 'role', Role::class),
        ];
    }

    public function pricingFields() : array
    {
        return [
            DateTime::make(__('Event Start'), 'event_start')
                ->rules('required', 'date')
                ->firstDayOfWeek(1),

            DateTime::make(__('Event End'), 'event_end')
                ->rules('required', 'date', 'after:event_start')
                ->firstDayOfWeek(1),

            Number::make(__('Member Price'), 'price_member')
                ->min(1)
                ->max(200)
                ->step(0.05)
                ->rules('nullable', 'numeric', 'min:2.50')
                ->help('Excluding transaction fees'),

            Number::make(__('Guest Price'), 'price_guest')
                ->min(1)
                ->max(200)
                ->step(0.05)
                ->rules('nullable', 'numeric', 'min:2.50', 'gte:price_member')
                ->help('Excluding transaction fees'),
        ];
    }

    public function enrollmentFields() : array
    {
        return [
            DateTime::make(__('Enrollment Start'), 'enrollment_start')
                ->rules('nullable', 'date', 'before:event_end')
                ->firstDayOfWeek(1),

            DateTime::make(__('Enrollment End'), 'enrollment_end')
                ->rules('nullable', 'date', 'before_or_equal:event_end')
                ->firstDayOfWeek(1),

            Number::make(__('Total Seats'), 'seats')
                ->min(1)
                ->step(1)
                ->rules('nullable', 'numeric', 'min:1'),

            Number::make(__('Guest Seats'), 'public_seats')
                ->min(1)
                ->step(1)
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
