<?php

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
use Laravel\Nova\Resource;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\PermissionRegistrar;
use Vyuldashev\NovaPermission\AttachToRole;

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
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Permissions');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Permission');
    }

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fields(Request $request)
    {
        $guardOptions = collect(config('auth.guards'))->keys()->mapWithKeys(function ($key) {
            return [$key => $key];
        });

        $userResource = Nova::resourceForModel(getModelForGuard($this->guard_name));

        return [
            ID::make()->sortable(),

            Text::make('Name', 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:permissions')
                ->updateRules('unique:permissions,name,{{resourceId}}'),

            Text::make('Title', 'title')
                ->rules(['required', 'string', 'max:255']),

            Select::make('Guard Name', 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)])
                ->hideFromIndex(),

            DateTime::make('Created At', 'created_at')
                ->onlyOnDetail(),
            DateTime::make('Updated At', 'updated_at')
                ->onlyOnDetail(),

            Number::make('Aantal Rollen', function () {
                return $this->roles()->count();
            })->onlyOnIndex(),
            Number::make('Aantal Gebruikers', function () {
                return $this->users()->count();
            })->onlyOnIndex(),

            BelongsToMany::make('Roles')
                ->searchable(),

            MorphToMany::make('Users', 'users', $userResource)
                ->searchable(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function actions(Request $request)
    {
        return [
            new AttachToRole(),
        ];
    }
}
