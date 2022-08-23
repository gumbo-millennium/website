<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\JoinSubmission as JoinSubmissionModel;
use App\Nova\Actions\HandleJoinSubmission;
use App\Nova\Metrics\NewJoinSubmissions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;
use Laravel\Nova\Panel;

/**
 * Returns join requests.
 */
class JoinSubmission extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = JoinSubmissionModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Name of the group.
     *
     * @var string
     */
    public static $group = 'Board';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'first_name',
        'last_name',
        'email',
        'postal_code',
        'street',
        'phone',
    ];

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        $sixteenYears = today()->subYear(16)->format('Y-m-d');

        return [
            new Panel('Basisinformatie', [
                Fields\ID::make()->sortable(),

                Fields\DateTime::make('Ontvangen op', 'created_at')
                    ->onlyOnDetail(),

                // Heading in form
                Fields\Heading::make('Persoonsgegevens')->onlyOnForms(),

                // Name
                Fields\Text::make('Naam', 'name')
                    ->sortable()
                    ->onlyOnIndex(),

                // Full name
                Fields\Text::make('Voornaam', 'first_name')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'min:2']),
                Fields\Text::make('Tussenvoegsel', 'insert')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'min:2']),
                Fields\Text::make('Achternaam', 'last_name')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'min:2']),

                // Date of Brith
                Fields\Date::make('Geboortedatum', 'date_of_birth')
                    ->hideFromIndex()
                    ->help('Geboortedatum')
                    ->rules([
                        'required',
                        'date_format:Y-m-d',
                        "before:{$sixteenYears}",
                    ]),
                Fields\Text::make('Geslacht', 'gender')
                    ->hideFromIndex()
                    ->help('Geslacht, in vrije vorm')
                    ->rules(['required']),
            ]),
            new Panel('Adres informatie', [
                Fields\Text::make('Adres', fn () => "{$this->street} {$this->number}")->onlyOnDetail(),

                // Heading in form
                Fields\Heading::make('Adres')->onlyOnForms(),

                Fields\Text::make('Straat', 'street')
                    ->onlyOnForms()
                    ->rules(['required', 'string', 'regex:/\w+/']),

                Fields\Text::make('Huisnummer', 'number')
                    ->onlyOnForms()
                    ->rules(['required', 'string', 'regex:/^\d+/']),

                Fields\Text::make('Postcode', 'postal_code')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'regex:/^[0-9A-Z \.]+$/i']),

                Fields\Text::make('Plaats', 'city')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'min:2']),
            ]),

            new Panel('Contact informatie', [
                // Heading in form
                Fields\Heading::make('Communicatie')->onlyOnForms(),

                Fields\Text::make('E-mailadres', 'email')
                    ->withMeta(['type' => 'email'])
                    ->rules(['required', 'email']),

                Fields\Text::make('Telefoonnummer', 'phone')
                    ->withMeta(['type' => 'phone'])
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'regex:/^\+?([\s-\.]?\d){8,}/']),
            ]),

            new Panel('Voorkeuren en inschrijvingsinformatie', [
                // Heading in form
                Fields\Heading::make('Voorkeuren en inschrijvingsinformatie')->onlyOnForms(),

                Fields\Boolean::make('Windesheim Student', 'windesheim_student')
                    ->hideFromIndex(),
                Fields\Boolean::make('Aanmelding Gumbode', 'newsletter')
                    ->hideFromIndex(),
                Fields\Text::make('Referentie', 'referrer')
                    ->hideFromIndex(),
            ]),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function actions(Request $request)
    {
        return [
            new HandleJoinSubmission(),
        ];
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function cards(Request $request)
    {
        return [
            new NewJoinSubmissions(),
        ];
    }
}
