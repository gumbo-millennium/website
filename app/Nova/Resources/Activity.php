<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJsField;
use App\Helpers\Str;
use App\Models\Activity as ActivityModel;
use App\Nova\Actions;
use App\Nova\Fields\Seats;
use App\Nova\Filters;
use App\Nova\Flexible\Presets\ActivityForm;
use App\Nova\Metrics;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Whitecube\NovaFlexibleContent\Flexible;

/**
 * An activity resource, highly linked.
 */
class Activity extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = ActivityModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = [
        'tickets',
        'enrollments',
    ];

    /**
     * Name of the group.
     *
     * @var string
     */
    public static $group = 'Activiteiten';

    /**
     * @inheritDoc
     */
    public static $defaultSort = 'start_date';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'tagline',
        'description',
    ];

    /**
     * Make sure the user can only see enrollments he/she is allowed to see.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return self::queryAllOrManaged($request, parent::indexQuery($request, $query));
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return self::queryAllOrManaged($request, parent::relatableQuery($request, $query));
    }

    /**
     * Build a Scout search query for the given resource.
     *
     * @param \Laravel\Scout\Builder $query
     * @return \Laravel\Scout\Builder
     */
    public static function scoutQuery(NovaRequest $request, $query)
    {
        return self::queryAllOrManaged($request, parent::scoutQuery($request, $query));
    }

    /**
     * Return query that is filtered on allowed activities, IF the user is
     * not allowed to view them all.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private static function queryAllOrManaged(NovaRequest $request, $query)
    {
        // User is admin, don't filter
        if ($request->user()->can('admin', ActivityModel::class)) {
            return $query;
        }

        // User only has a subset of queries, filter it
        return $request->user()->getHostedActivityQuery($query);
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        // Format some dates
        $startDate = $this->start_date?->format('d-m-Y');
        $endDate = $this->end_date?->format('d-m-Y');
        $startTime = $this->start_date?->format('H:i');
        $endTime = $this->end_date?->format('H:i');

        // If the start date isn't the end date, show both
        if ($startDate !== $endDate) {
            return sprintf('%s – %s', $startDate, $endDate);
        }

        // Otherwise, show date and time
        return sprintf('%s (%s - %s)', $startDate, $startTime, $endTime);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $featuresMap = collect(Config::get('gumbo.activity-features', []))
            ->mapWithKeys(fn ($row, $key) => [$key => $row['title']])
            ->all();

        return [
            $this->mainFields($request),

            new Panel('Evenement-details', [
                Fields\Text::make('Weergavenaam locatie', 'location')
                    ->hideFromIndex()
                    ->rules('required', 'string', 'between:2,64')
                    ->help('Weergavenaam van de locatie.'),

                Fields\Text::make('Adres locatie', 'location_address')
                    ->hideFromIndex()
                    ->rules([
                        'max:190',
                    ])
                    ->help(<<<'LOCATION'
                        Adres van de locatie, mag een webadres zijn.
                    LOCATION),

                Fields\BooleanGroup::make('Eigenschappen', 'features')
                    ->options($featuresMap)
                    ->help('Extra eigenschappen om aan deze activiteit toe te voegen.'),
            ]),

            new Panel('Datums', $this->dateFields()),

            new Panel('Inschrijf-instellingen', $this->enrollmentFields()),

            Fields\HasMany::make('Inschrijvingen', 'enrollments', Enrollment::class),
            Fields\HasMany::make(__('Messages'), 'messages', ActivityMessage::class),
        ];
    }

    public function mainFields(NovaRequest $request): MergeValue
    {
        $user = $request->user();
        $groupRules = $user->can('admin', self::class) ? 'nullable' : 'required';

        return $this->merge([
            Fields\ID::make()->sortable(),

            Fields\Text::make('Titel', 'name')
                ->sortable()
                ->rules([
                    'required',
                    'between:4,255',
                ]),

            Fields\Slug::make('Pad', 'slug')
                ->from('name')
                ->creationRules('unique:activities,slug')
                ->help('Het pad naar deze activiteit (/activiteiten/[pad])')
                ->readonly(fn () => $this->exists)
                ->hideFromIndex()
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $slug = $request->input($requestAttribute);
                    $model->{$attribute} = Str::slug($slug, '-', 'nl');
                }),

            Fields\Text::make('Slagzin', 'tagline')
                ->hideFromIndex()
                ->help('Korte slagzin om de activiteit te omschrijven')
                ->rules('nullable', 'string', 'between:4,255'),

            Fields\BelongsTo::make('Groep', 'role', Role::class)
                ->help('Groep of commissie die deze activiteit beheert')
                ->rules($groupRules)
                ->hideFromIndex()
                ->nullable(),

            NovaEditorJsField::make('Omschrijving', 'description')
                ->nullable()
                ->hideFromIndex()
                ->stacked(),

            Fields\Image::make('Afbeelding', 'poster')
                ->disk(Config::get('gumbo.images.disk'))
                ->path(path_join(Config::get('gumbo.images.path'), 'activities'))
                ->thumbnail(fn () => (string) image_asset($this->poster)->preset('nova-thumbnail'))
                ->preview(fn () => (string) image_asset($this->poster)->preset('nova-preview'))
                ->deletable()
                ->nullable()
                ->acceptedTypes(['image/png', 'image/jpeg'])
                ->help('Afbeelding die bij de activiteit en op Social Media getoond wordt, in 3:1 verhouding')
                ->rules(
                    'nullable',
                    'image',
                    'mimes:jpeg,png',
                    'max:2048',
                    Rule::dimensions()
                        ->minWidth(768)
                        ->minHeight(256),
                ),

            Fields\DateTime::make('Aangemaakt op', 'created_at')
                ->readonly()
                ->onlyOnDetail(),

            Fields\DateTime::make('Laatst bewerkt op', 'updated_at')
                ->readonly()
                ->onlyOnDetail(),

            Fields\DateTime::make('Geannuleerd op', 'cancelled_at')
                ->readonly()
                ->onlyOnDetail(),

            Fields\Text::make('Geannuleerd om', 'cancelled_reason')
                ->readonly()
                ->onlyOnDetail(),

            Fields\DateTime::make('Publiceren op', 'published_at')
                ->help('Indien je de activiteit nog even wilt verbergen. Dit werkt hetzelfde als een ‘unlisted’ video op YouTube')
                ->rules('nullable', 'date', 'before:start_date')
                ->nullable()
                ->hideFromIndex(),
        ]);
    }

    /**
     * Pricing fields.
     */
    public function dateFields(): array
    {
        return [
            Fields\DateTime::make('Aanvang activiteit', 'start_date')
                ->sortable()
                ->rules('required', 'date')
                ->help('Let op! Als de activiteit (door overmacht) ver is verplaatst, gebruik dan "Verplaats activiteit"'),

            Fields\DateTime::make('Einde activiteit', 'end_date')
                ->rules('required', 'date', 'after:start_date')
                ->hideFromIndex(),
        ];
    }

    public function enrollmentFields(): array
    {
        return [
            Fields\Number::make(__('Ticket Count'), 'tickets_count')
                ->exceptOnForms()
                ->help(__('Number of different tickets available.')),

            Flexible::make('Form', 'enrollment_questions')
                ->confirmRemove('Removing a field does not remove submitted data')
                ->preset(ActivityForm::class),

            Fields\DateTime::make('Opening inschrijvingen', 'enrollment_start')
                ->rules('nullable', 'date', 'before:end_date')
                ->hideFromIndex()
                ->nullable(),

            Fields\DateTime::make('Sluiting inschrijvingen', 'enrollment_end')
                ->rules('nullable', 'date', 'before_or_equal:end_date')
                ->hideFromIndex()
                ->nullable(),

            NovaEditorJsField::make('Ticket omschrijving', 'ticket_text')
                ->help('De tekst die je op het ticket wil tonen. Vooral nuttig voor openbare evenementen.')
                ->nullable()
                ->hideFromIndex()
                ->stacked(),

            Fields\Text::make('Status inschrijvingen', function () {
                // Edge case for no-enrollment events
                if ($this->enrollment_start === null && $this->enrollment_end === null) {
                    return 'n.v.t.';
                }

                // Label
                return $this->enrollment_open ? 'Geopend' : 'Gesloten';
            })->onlyOnIndex(),

            Seats::make('Maximaal aantal plekken', 'seats')
                ->min(0)
                ->step(1)
                ->nullable()
                ->nullValues(['', '0'])
                ->rules('nullable', 'numeric', 'min:0')
                ->help('Het absolute maximum aantal plekken, indien je dit niet met tickets wil doen.'),

            // Public
            Fields\Boolean::make('Openbare activiteit', 'is_public'),

            // Computed published flag
            Fields\Boolean::make('Gepubliceerd', 'is_published')
                ->onlyOnIndex(),

            // Ticket
            Fields\HasMany::make(__('Tickets'), 'tickets', Ticket::class)
                ->nullable()
                ->hideFromIndex(),
        ];
    }

    /**
     * Get the actions available on the entity.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            new Actions\CancelActivity(),
            new Actions\PostponeActivity(),
            new Actions\RescheduleActivity(),
            new Actions\SendActivityMail(),
            new Actions\ExportActivityParticipants(),
        ];
    }

    /**
     * Get the filters available on the entity.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new Filters\RelevantActivitiesFilter(),
        ];
    }

    public function cards(Request $request)
    {
        return [
            new Metrics\NewEnrollments(),
            new Metrics\PendingEnrollments(),
            new Metrics\ConfirmedEnrollments(),
        ];
    }
}
