<?php

namespace App\Nova\Resources;

use App\Models\User as UserModel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\MorphToMany;

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

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Users');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('User');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->sortable()
                ->onlyOnIndex(),

            Text::make('First Name')
                ->hideFromIndex()
                ->rules('required', 'max:255'),

            Text::make('Insert')
                ->hideFromIndex()
                ->rules('nullable', 'max:255'),

            Text::make('Last Name')
                ->hideFromIndex()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make('Alias')
                ->rules('nullable', 'between:2,60'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8'),

            // Permissions
            MorphToMany::make('Roles', 'roles', \Vyuldashev\NovaPermission\Role::class),
            MorphToMany::make('Permissions', 'permissions', \Vyuldashev\NovaPermission\Permission::class),
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
