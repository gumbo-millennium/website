<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles, for the Permission Framework.
 */
class Role extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = RoleModel::class;

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
     * Hide the item in the navbar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    public static function getModel()
    {
        return app(PermissionRegistrar::class)->getRoleClass();
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
        // Get user shorthand
        $user = $request->user();
        \assert($user instanceof User);

        // Return all roles if the user is allowed to admin events
        if ($user->can('admin', Activity::class) || $user->can('manage', RoleModel::class)) {
            return parent::relatableQuery($request, $query);
        }

        // Only return own roles in
        return parent::relatableQuery($request, $query->whereIn(
            'id',
            $user->roles->pluck('id')
        ));
    }

    /**
     * Get the fields displayed by the resource.
     *
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
                ->creationRules('unique:roles')
                ->updateRules('unique:roles,name,{{resourceId}}'),

            Text::make('Titel', 'title')
                ->rules(['required', 'string', 'max:255']),

            Select::make('Guard Name', 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)])
                ->hideFromIndex(),

            DateTime::make('Aangemaakt op', 'created_at')
                ->onlyOnDetail(),
            DateTime::make('Laatst bewerkt op', 'updated_at')
                ->onlyOnDetail(),

            Number::make('Aantal permissies', fn () => $this->permissions()->count())->onlyOnIndex(),
            Number::make('Aantal gebruikers', fn () => $this->users()->count())->onlyOnIndex(),

            BelongsToMany::make('permissies', 'permissions', Permission::class)
                ->searchable(),

            MorphToMany::make('gebruikers', 'users', $userResource)
                ->searchable(),
        ];
    }
}
