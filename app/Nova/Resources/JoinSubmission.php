<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\JoinSubmission as JoinSubmissionModel;
use App\Nova\Actions\HandleJoinSubmission;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * Returns join requests
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JoinSubmission extends Resource
{
    /**
     * The model the resource corresponds to.
     * @var string
     */
    public static $model = JoinSubmissionModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     * @var string
     */
    public static $title = 'name';

    /**
     * Name of the group
     * @var string
     */
    public static $group = 'Bestuurszaken';

    /**
     * The columns that should be searched.
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

    /**
     * Get the displayable label of the resource.
     * @return string
     */
    public static function label()
    {
        return 'Lidmaatschapsverzoeken';
    }

    /**
     * Get the displayable singular label of the resource.
     * @return string
     */
    public static function singularLabel()
    {
        return 'Lidmaatschapsverzoek';
    }

    /**
     * Get the fields displayed by the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        $sixteenYears = today()->subYear(16)->format('Y-m-d');

        return [
            new Panel('Basisinformatie', [
                ID::make()->sortable(),

                DateTime::make('Ontvangen op', 'created_at')
                    ->onlyOnDetail(),

                // Heading in form
                Heading::make('Persoonsgegevens')->onlyOnForms(),

                // Name
                Text::make('Naam', 'name')
                    ->sortable()
                    ->onlyOnIndex(),

                // Full name
                Text::make('Voornaam', 'first_name')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'min:2']),
                Text::make('Tussenvoegsel', 'insert')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'min:2']),
                Text::make('Achternaam', 'last_name')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'min:2']),

                // Date of Brith
                Text::make('Geboortedatum', 'date_of_birth')
                    ->hideFromIndex()
                    ->help('Geboortedatum, in ISO 8601 (yyyy-mm-dd)')
                    ->rules(['required', 'date_format:Y-m-d', "before:{$sixteenYears}"]),
            ]),
            new Panel('Adres informatie', [
                Text::make('Adres', fn () => "{$this->street} {$this->number}")->onlyOnDetail(),

                // Heading in form
                Heading::make('Adres')->onlyOnForms(),

                Text::make('Straat', 'street')
                    ->onlyOnForms()
                    ->rules(['required', 'string', 'regex:/\w+/']),

                Text::make('Huisnummer', 'number')
                    ->onlyOnForms()
                    ->rules(['required', 'string', 'regex:/^\d+/']),

                Text::make('Postcode', 'postal_code')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'regex:/^[0-9A-Z \.]+$/i']),

                Text::make('Plaats', 'city')
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'min:2']),
            ]),

            new Panel('Contact informatie', [
                // Heading in form
                Heading::make('Communicatie')->onlyOnForms(),

                Text::make('E-mailadres', 'email')
                    ->withMeta(['type' => 'email'])
                    ->rules(['required', 'email']),

                Text::make('Telefoonnummer', 'phone')
                    ->withMeta(['type' => 'phone'])
                    ->hideFromIndex()
                    ->rules(['required', 'string', 'regex:/^\+?([\s-\.]?\d){8,}/']),
            ]),

            new Panel('Voorkeuren en inschrijvingsinformatie', [
                // Heading in form
                Heading::make('Voorkeuren en inschrijvingsinformatie')->onlyOnForms(),

                Boolean::make('Windesheim Student', 'windesheim_student')
                    ->hideFromIndex(),
                Boolean::make('Aanmelding Gumbode', 'newsletter')
                    ->hideFromIndex(),
                Text::make('Referentie', 'referrer')
                    ->hideFromIndex()
            ]),

        ];
    }

    /**
     * Get the actions available for the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function actions(Request $request)
    {
        return [
            new HandleJoinSubmission(),
        ];
    }
}
