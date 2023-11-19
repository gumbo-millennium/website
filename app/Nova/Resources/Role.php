<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields;
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
        $user = $request->user();
        $relatedResource = $request->relatedResource();

        if (empty($relatedResource) || ! in_array([User::class, Permission::class], $relatedResource, true)) {
            $query->whereNotNull('conscribo_id');
        }

        // Don't allow free-assignment unless users can cross-assign
        if (! $user->can('manage', Activity::class)) {
            $query->whereIn('id', $user->roles->pluck('id'));
        }

        // Only return own roles in
        return parent::relatableQuery($request, $query);
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
            Fields\ID::make()->sortable(),

            Fields\Text::make(__('Name'), 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:roles')
                ->updateRules('unique:roles,name,{{resourceId}}'),

            Fields\Text::make(__('Title'), 'title')
                ->rules(['required', 'string', 'max:255']),

            Fields\Boolean::make(__('Linked to Conscribo'), fn () => $this->conscribo_id !== null)
                ->onlyOnDetail(),

            Fields\Select::make(__('Guard Name'), 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)])
                ->hideFromIndex(),

            Fields\DateTime::make(__('Created At'), 'created_at')
                ->onlyOnDetail(),
            Fields\DateTime::make(__('Updated At'), 'updated_at')
                ->onlyOnDetail(),

            Fields\Number::make(__('Permission Count'), fn () => $this->permissions()->count())->onlyOnIndex(),
            Fields\Number::make(__('User Count'), fn () => $this->users()->count())->onlyOnIndex(),

            Fields\BelongsToMany::make(__('Permissions'), 'permissions', Permission::class)
                ->searchable(),

            Fields\MorphToMany::make(__('Users'), 'users', $userResource)
                ->searchable(),
        ];
    }
}
