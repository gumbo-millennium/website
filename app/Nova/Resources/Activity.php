<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Activity as ActivityModel;
use App\Nova\Actions\CancelActivity;
use App\Nova\Actions\PostponeActivity;
use App\Nova\Actions\RescheduleActivity;
use App\Nova\Fields\Price;
use App\Nova\Fields\Seats;
use App\Nova\Filters\RelevantActivitiesFilter;
use App\Nova\Flexible\Presets\ActivityForm;
use App\Nova\Metrics\ConfirmedEnrollments;
use App\Nova\Metrics\NewEnrollments;
use App\Nova\Metrics\PendingEnrollments;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Activity extends Resource
{
    /**
     * The model the resource corresponds to.
     * @var string
     */
    public static $model = ActivityModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     * @var string
     */
    public static $title = 'name';

    /**
     * Name of the group
     * @var string
     */
    public static $group = 'Activiteiten';

    /**
     * @inheritDoc
     */
    public static $defaultSort = 'start_date';


    /**
     * The columns that should be searched.
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'tagline',
        'description',
    ];

    /**
     * Make sure the user can only see enrollments he/she is allowed to see
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
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Scout\Builder  $query
     * @return \Laravel\Scout\Builder
     */
    public static function scoutQuery(NovaRequest $request, $query)
    {
        return self::queryAllOrManaged($request, parent::scoutQuery($request, $query));
    }

    /**
     * Return query that is filtered on allowed activities, IF the user is
     * not allowed to view them all
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
     * Get the search result subtitle for the resource.
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        return [
            $this->mainFields($request),
            new Panel('Datum en prijs-instellingen', $this->pricingFields()),
            new Panel('Inschrijf-instellingen', $this->enrollmentFields()),

            HasMany::make('Inschrijvingen', 'enrollments', Enrollment::class),
        ];
    }

    public function mainFields(Request $request): MergeValue
    {
        $user = $request->user();
        $groupRules = $user->can('admin', Activity::class) ? 'nullable' : 'required';

        return $this->merge([
            ID::make()->sortable(),

            TextWithSlug::make('Titel', 'name')
                ->sortable()
                ->slug('slug')
                ->rules('required', 'between:4,255'),

            Slug::make('Pad', 'slug')
                ->creationRules('unique:activities,slug')
                ->help('Het pad naar deze activiteit (/activiteiten/[pad])')
                ->readonly(fn () => $this->exists),

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
                ->rules('required', 'string', 'between:2,64')
                ->help('Weergavenaam van de locatie.'),

            Text::make('Adres locatie', 'location_address')
                ->hideFromIndex()
                ->rules('required_unless:location_type,online', 'max:190')
                ->help(<<<'LOCATION'
                    Het adres van de locatie, indien niet geheel online.
                    Houd, indien onbekend of geheim, "Zwolle, Netherlands" aan.
                LOCATION),

            Select::make('Type locatie', 'location_type')
                ->hideFromIndex()
                ->options([
                    ActivityModel::LOCATION_OFFLINE => 'Geheel offline',
                    ActivityModel::LOCATION_ONLINE => 'Geheel online',
                    ActivityModel::LOCATION_MIXED => 'Gemixt',
                ])
                ->help('Het type locatie, kan iemand vanuit huis meedoen of alleen op locatie?')
                ->rules('required'),

            NovaEditorJs::make('Omschrijving', 'description')
                ->nullable()
                ->hideFromIndex()
                ->stacked(),

            PaperclipImage::make('Afbeelding', 'image')
                ->deletable()
                ->nullable()
                ->mimes(['png', 'jpeg', 'jpg'])
                ->help('Afbeelding die bij de activiteit en op Social Media getoond wordt, in 3:1 verhouding')
                ->minWidth(640)
                ->minHeight(480)
                ->rules(
                    'nullable',
                    'image',
                    'mimes:jpeg,png',
                    'max:2048',
                    Rule::dimensions()
                        ->maxWidth(3072)
                        ->maxHeight(1024)
                        ->minWidth(768)
                        ->minHeight(256)
                ),

            DateTime::make('Aangemaakt op', 'created_at')
                ->readonly()
                ->onlyOnDetail(),

            DateTime::make('Laatst bewerkt op', 'updated_at')
                ->readonly()
                ->onlyOnDetail(),

            DateTime::make('Geannuleerd op', 'cancelled_at')
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Geannuleerd om', 'cancelled_reason')
                ->readonly()
                ->onlyOnDetail(),

            BelongsTo::make('Groep', 'role', Role::class)
                ->help('Groep of commissie die deze activiteit beheert')
                ->rules($groupRules)
                ->hideFromIndex()
                ->nullable(),
        ]);
    }

    /**
     * Pricing fields
     * @return array
     */
    public function pricingFields(): array
    {
        return [
            DateTime::make('Aanvang activiteit', 'start_date')
                ->sortable()
                ->rules('required', 'date')
                ->firstDayOfWeek(1)
                // phpcs:ignore Generic.Files.LineLength.TooLong
                ->help('Let op! Als de activiteit (door overmacht) ver is verplaatst, gebruik dan "Verplaats activiteit"'),

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
                ->min(0)
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

            Flexible::make('Form', 'enrollment_questions')
                ->confirmRemove('Removing a field does not remove submitted data')
                ->preset(ActivityForm::class)
        ];
    }

    public function enrollmentFields(): array
    {
        return [
            DateTime::make('Publiceren op', 'published_at')
                // phpcs:ignore Generic.Files.LineLength.TooLong
                ->help('Indien je de activiteit nog even wilt verbergen. Dit werkt hetzelfde als een ‘unlisted’ video op YouTube')
                ->rules('nullable', 'date', 'before:start_date')
                ->nullable()
                ->hideFromIndex(),

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
                return $this->enrollment_open ? 'Geopend' : 'Gesloten';
            })->onlyOnIndex(),

            Seats::make('Aantal plekken', 'seats')
                ->min(0)
                ->step(1)
                ->nullable()
                ->nullValues(['', '0'])
                ->rules('nullable', 'numeric', 'min:0'),

            // Public
            Boolean::make('Openbare activiteit', 'is_public'),

            // Computed published flag
            Boolean::make('Gepubliceerd', 'is_published')
                ->onlyOnIndex(),
        ];
    }

    /**
     * Get the actions available on the entity.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function actions(Request $request)
    {
        return [
            new CancelActivity(),
            new PostponeActivity(),
            new RescheduleActivity()
        ];
    }

    /**
     * Get the filters available on the entity.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new RelevantActivitiesFilter()
        ];
    }

    /**
     * @inheritdoc
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function cards(Request $request)
    {
        return [
            new NewEnrollments(),
            new PendingEnrollments(),
            new ConfirmedEnrollments(),
        ];
    }
}
