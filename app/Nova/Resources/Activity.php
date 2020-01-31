<?php

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Activity as ActivityModel;
use App\Nova\Fields\Price;
use App\Nova\Fields\Seats;
use App\Nova\Flexible\Presets\ActivityForm;
use App\Policies\ActivityPolicy;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use DanielDeWit\NovaPaperclip\PaperclipImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Whitecube\NovaFlexibleContent\Flexible;

/**
 * An activity resource, highly linked
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * Name of the group
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
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Activiteiten';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Activiteit';
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        // Format some dates
        $startDate = optional($this->start_date)->format('d-m-Y');
        $endDate = optional($this->end_date)->format('d-m-Y');
        $startTime = optional($this->start_date)->format('H:i');
        $endTime = optional($this->end_date)->format('H:i');

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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fields(Request $request)
    {
        return [
            $this->mainFields(),
            new Panel('Date and price settings', $this->pricingFields()),
            new Panel('Enrollment settings', $this->enrollmentFields()),

            HasMany::make('Enrollments', 'enrollments', Enrollment::class),
        ];
    }

    public function mainFields(): MergeValue
    {
        return $this->merge([
            ID::make()->sortable(),

            TextWithSlug::make('Titel', 'name')
                ->sortable()
                ->slug('slug')
                ->rules('required', 'between:4,255'),

            Slug::make('Pad', 'slug')
                ->creationRules('unique:activities,slug')
                ->help('Het pad naar deze activiteit (/activiteiten/[pad])')
                ->readonly(function () {
                    return $this->exists;
                }),

            Text::make('Slagzin', 'tagline')
                ->hideFromIndex()
                ->help('Korte slagzin om de activiteit te omschrijven')
                ->rules('nullable', 'string', 'between:4,255'),

            Text::make('Incasso-omschrijving', 'statement')
                ->hideFromIndex()
                ->rules('nullable', 'string', 'between:2,16')
                ->help('2-16 tekens lange omschrijng, welke op het iDEAL afschrift getoond wordt.'),

            Text::make('Weergavenaam locatie', 'location')
                ->hideFromIndex()
                ->rules('nullable', 'string', 'between:2,64')
                ->help('Weergavenaam van de activiteit'),

            Text::make('Adres locatie', 'location_address')
                ->hideFromIndex()
                ->rules('nullable', 'string', 'between:5,255')
                ->help('Het adres van de locatie. Wordt doorgegeven aan Quant Maps'),

            NovaEditorJs::make('Omschrijving', 'description')
                ->nullable()
                ->hideFromIndex()
                ->stacked(),

            PaperclipImage::make('Afbeelding', 'image')
                ->deletable()
                ->nullable()
                ->mimes(['png', 'jpeg', 'jpg'])
                ->help('Afbeelding die op de detailpagina en op Social Media getoond wordt. Maximaal 2MB')
                ->minWidth(1920)
                ->minHeight(960)
                ->rules(
                    'nullable',
                    'image',
                    'mimes:jpeg,png',
                    'size:2048',
                    Rule::dimensions()->maxWidth(1920)->maxHeight(600)
                ),

            DateTime::make('Aangemaakt op', 'created_at')
                ->readonly()
                ->onlyOnDetail(),

            DateTime::make('Laatst bewerkt op', 'updated_at')
                ->readonly()
                ->onlyOnDetail(),

            BelongsTo::make('Groep', 'role', Role::class)
                ->help('Groep of commissie die deze activiteit beheert')
                ->hideFromIndex()
                ->nullable(),
        ]);
    }

    /**
     * Pricing fields
     *
     * @return array
     */
    public function pricingFields(): array
    {
        return [
            DateTime::make('Aanvang activiteit', 'start_date')
                ->sortable()
                ->rules('required', 'date')
                ->firstDayOfWeek(1),

            DateTime::make('Einde activiteit', 'end_date')
                ->rules('required', 'date', 'after:start_date')
                ->hideFromIndex()
                ->firstDayOfWeek(1),

            Price::make('Netto prijs', 'price')
                ->min(2.50)
                ->max(200)
                ->step(0.25)
                ->nullable()
                ->nullValues([''])
                ->rules('nullable', 'numeric', 'min:2.50')
                ->help('In euro, exclusief transactiekosten'),

            Price::make('Totaalprijs', 'total_price')
                ->help('In euro, inclusief transactiekosten')
                ->onlyOnDetail(),

            Price::make('Korting leden', 'member_discount')
                ->min(0.50)
                ->max(200)
                ->step(0.25)
                ->nullable()
                ->nullValues([''])
                ->rules('nullable', 'numeric', 'min:0.50', 'lte:price')
                ->help('In euro')
                ->onlyOnForms(),

            Price::make('Totaalprijs korting', 'total_discount_price')
                ->help('In euro, inclusief transactiekosten')
                ->onlyOnDetail(),

            Number::make('Aantal kortingen', 'discount_count')
                ->step(1)
                ->nullable()
                ->rules('nullable', 'numeric', 'min:1')
                ->help('Beperkt het aantal keer dat de korting wordt verleend.'),

            // Flexible::make('Form', 'enrollment_questions')
            //     ->confirmRemove('Removing a field does not remove submitted data')
            //     ->preset(ActivityForm::class)
        ];
    }

    public function enrollmentFields(): array
    {
        return [
            DateTime::make('Opening inschrijvingen', 'enrollment_start')
                ->rules('nullable', 'date', 'before:end_date')
                ->hideFromIndex()
                ->nullable()
                ->firstDayOfWeek(1),

            DateTime::make('Sluiting inschrijvingen', 'enrollment_end')
                ->rules('nullable', 'date', 'before_or_equal:end_date')
                ->hideFromIndex()
                ->nullable()
                ->firstDayOfWeek(1),

            Text::make('Status inschrijvingen', function () {
                // Edge case for no-enrollment events
                if ($this->enrollment_start === null && $this->enrollment_end === null) {
                    return 'n.v.t.';
                }

                // Label
                return $this->enrollment_status ? 'Geopend' : 'Gesloten';
            })->onlyOnIndex(),

            Seats::make('Aantal plekken', 'seats')
                ->min(0)
                ->step(1)
                ->nullable()
                ->nullValues(['', '0'])
                ->rules('nullable', 'numeric', 'min:0'),

            Boolean::make('Openbare activiteit', 'is_public'),
        ];
    }

    /**
     * Return query that is filtered on allowed activities, IF the user is
     * not allowed to view them all
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
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
     * Make sure the user can only see enrollments he/she is allowed to see
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
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
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return self::queryAllOrManaged($request, parent::relatableQuery($request, $query));
    }

    /**
     * Build a Scout search query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Scout\Builder  $query
     * @return \Laravel\Scout\Builder
     */
    public static function scoutQuery(NovaRequest $request, $query)
    {
        return self::queryAllOrManaged($request, parent::scoutQuery($request, $query));
    }
}
