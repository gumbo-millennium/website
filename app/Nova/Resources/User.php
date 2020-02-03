<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\User as UserModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;

/**
 * Users of our system
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class User extends Resource
{
    /**
     * The model the resource corresponds to.
     * @var string
     */
    public static $model = UserModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     * @var array
     */
    public static $search = [
        'id',
        'first_name',
        'last_name',
        'email',
        'alias',
    ];

    /**
     * Get the displayable label of the resource.
     * @return string
     */
    public static function label()
    {
        return 'Gebruikers';
    }

    /**
     * Get the displayable singular label of the resource.
     * @return string
     */
    public static function singularLabel()
    {
        return 'Gebruiker';
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
            ID::make()->sortable(),

            Text::make('Naam', 'name')
                ->sortable()
                ->onlyOnIndex(),

            Text::make('Voornaam', 'first_name')
                ->hideFromIndex()
                ->rules('required', 'max:255'),

            Text::make('Tussenvoegsel', 'insert')
                ->hideFromIndex()
                ->rules('nullable', 'max:255'),

            Text::make('Achternaam', 'last_name')
                ->hideFromIndex()
                ->rules('required', 'max:255'),

            Text::make('E-mailadres', 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make('Alias', 'alias')
                ->rules('nullable', 'between:2,60'),

            Password::make('Wachtwoord', 'password')
                ->onlyOnForms()
                ->showOnUpdating(false)
                ->rules('required', 'string', 'min:10'),

            // Permissions
            MorphToMany::make('Rollen', 'roles', Role::class),
            MorphToMany::make('Permissies', 'permissions', Permission::class),
        ];
    }
}
