<?php

namespace App\Nova\Resources;

use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\BelongsToMany;
use Spatie\Permission\PermissionRegistrar;

class Role extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Spatie\Permission\Models\Role::class;

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
        return __('Roles');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Role');
    }

    public static function getModel()
    {
        return app(PermissionRegistrar::class)->getRoleClass();
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $guardOptions = collect(config('auth.guards'))->mapWithKeys(function ($value, $key) {
            return [$key => $key];
        });

        return [
            ID::make()->sortable(),

            Text::make('Name', 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:roles')
                ->updateRules('unique:roles,name,{{resourceId}}'),

            Text::make('Title', 'title')
                ->rules(['required', 'string', 'max:255']),

            Select::make('Guard Name', 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)]),

            DateTime::make('Created At', 'created_at')->exceptOnForms(),
            DateTime::make('Updated At', 'updated_at')->exceptOnForms(),

            BelongsToMany::make('Permissions')
                ->searchable(),

            MorphToMany::make('Users')
                ->searchable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
