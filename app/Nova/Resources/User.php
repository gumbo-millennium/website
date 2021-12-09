<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\User as UserModel;
use App\Nova\Filters\UserRoleFilter;
use App\Nova\Metrics\NewUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Fields;

/**
 * Users of our system.
 */
class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = UserModel::class;

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
        'first_name',
        'last_name',
        'email',
        'alias',
    ];

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make('Naam', 'name')
                ->sortable()
                ->onlyOnIndex(),

            Fields\Text::make('Voornaam', 'first_name')
                ->hideFromIndex()
                ->rules('required', 'max:255'),

            Fields\Text::make('Tussenvoegsel', 'insert')
                ->hideFromIndex()
                ->rules('nullable', 'max:255'),

            Fields\Text::make('Achternaam', 'last_name')
                ->hideFromIndex()
                ->rules('required', 'max:255'),

            Fields\Text::make('E-mailadres', 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->hideFromIndex(fn (Request $request) => Gate::denies('view', $this->model)),

            Fields\Text::make('Alias', 'alias')
                ->rules('nullable', 'between:2,60'),

            Fields\Boolean::make('E-mailadres bevestigd', fn () => $this->hasVerifiedEmail())
                ->onlyOnDetail(),

            Fields\Boolean::make('Lid', fn () => $this->is_member)
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            // Permissions
            Fields\MorphToMany::make('Rollen', 'roles', Role::class),
            Fields\MorphToMany::make('Permissies', 'permissions', Permission::class),

            // Enrollments
            Fields\HasMany::make('Inschrijvingen', 'enrollments', Enrollment::class),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function filters(Request $request)
    {
        return [
            new UserRoleFilter(),
        ];
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function cards(Request $request)
    {
        return [
            new NewUsers(),
        ];
    }
}
