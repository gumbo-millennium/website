<?php

declare(strict_types=1);

namespace App\Nova\Resources\Minisite;

use App\Models\Minisite\Site as SiteModel;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;

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

            Fields\Stack::make('Meta', [
                Fields\Text::make('Naam', 'name'),
                Fields\Text::make('Domeinnaam', 'domain')
                    ->displayUsing(fn ($domain) => "https://{$domain}/")
                    ->readonly(),
            ]),

            Fields\Boolean::make('Actief', 'enabled'),

            Fields\HasMany::make('Pagina\'s', 'pages', SitePage::class),
        ];
    }
}
