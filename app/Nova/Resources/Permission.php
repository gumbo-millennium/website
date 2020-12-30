<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Nova;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\PermissionRegistrar;
use Vyuldashev\NovaPermission\AttachToRole;

/**
 * Permissions, for the Permission Framework
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Permission extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PermissionModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'title',
    ];

    /**
     * Hide the item in the navbar
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Returns the model for the permission
     *
     * @return void
     */
    public static function getModel()
    {
        return app(PermissionRegistrar::class)->getPermissionClass();
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        $guardOptions = collect(config('auth.guards'))->keys()->mapWithKeys(static fn ($key) => [$key => $key]);

        $userResource = Nova::resourceForModel(getModelForGuard($this->guard_name));

        return [
            ID::make()->sortable(),

            Text::make('Naam', 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:permissions')
                ->updateRules('unique:permissions,name,{{resourceId}}'),

            Text::make('Titel', 'title')
                ->rules(['required', 'string', 'max:255']),

            Select::make('Guard naam', 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)])
                ->hideFromIndex(),

            DateTime::make('Aangemaakt op', 'created_at')
                ->onlyOnDetail(),
            DateTime::make('Laatst bewerkt op', 'updated_at')
                ->onlyOnDetail(),

            Number::make('Aantal rollen', fn () => $this->roles()->count())->onlyOnIndex(),
            Number::make('Aantal gebruikers', fn () => $this->users()->count())->onlyOnIndex(),

            BelongsToMany::make('Rollen', 'roles', Role::class)
                ->searchable(),

            MorphToMany::make('Gebruikers', 'users', $userResource)
                ->searchable(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function actions(Request $request)
    {
        return [
            new AttachToRole(),
        ];
    }
}
