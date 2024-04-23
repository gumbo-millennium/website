<?php

declare(strict_types=1);

namespace App\Nova\Resources\Minisite;

use App\Models\Minisite\Site as SiteModel;
use App\Nova\Resources\Activity;
use App\Nova\Resources\Resource;
use App\Nova\Resources\Role;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Minisites.
 */
class Site extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = SiteModel::class;

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
    public static $group = 'Minisites';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'domain',
        'name',
    ];

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'minisites';
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Minisites';
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $user = $request->user();
        if (! $user->can('admin', SiteModel::class)) {
            $query->whereIn('group_id', $user->roles->pluck('id'));
        }

        return parent::indexQuery($request, $query);
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return null|string
     */
    public function subtitle()
    {
        return "https://{$this->domain}/";
    }

    /**
     * Shorter list on index page.
     */
    public function fields(Request $request): array
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make('Naam', 'name')
                ->rules([
                    'required',
                    'string',
                    'between:3,100',
                ]),

            Fields\Text::make('Domeinnaam', fn ($model) => "https://{$model->domain}/"),

            Fields\Boolean::make('Actief', 'enabled'),

            Fields\BelongsTo::make('Groep', 'group', Role::class)
                ->readonly(fn (Request $request) => ! $request->user()->can('admin', SiteModel::class)),

            Fields\BelongsTo::make('Activiteit', 'activity', Activity::class)
                ->nullable(),

            Fields\HasMany::make('Pagina\'s', 'pages', SitePage::class),
        ];
    }
}
