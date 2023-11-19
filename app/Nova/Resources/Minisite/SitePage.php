<?php

declare(strict_types=1);

namespace App\Nova\Resources\Minisite;

use App\Enums\Models\Minisite\PageType;
use App\Models\Minisite\SitePage as SitePageModel;
use App\Nova\Fields\EditorJs;
use App\Nova\Filters\MinisiteFilter;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Pages for Minisites.
 */
class SitePage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = SitePageModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

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
        'title',
        'slug',
    ];

    /**
     * The relationships that should be eager loaded when performing an index query.
     */
    public static $with = [
        'site',
    ];

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'minisite-pages';
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Minisite Pagina\'s';
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
        if (! $user->can('admin', SitePageModel::class)) {
            $query->whereHas('site', fn ($query) => $query->whereIn('group_id', $user->roles->pluck('id')));
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
        return "https://{$this->site}/{$this->slug}";
    }

    /**
     * Shorter list on index page.
     */
    public function fieldsForIndex(Request $request): array
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\BelongsTo::make('Site', 'site', Site::class)
                ->filterable(),

            Fields\Stack::make('Meta', [
                Fields\Text::make('Titel', 'title'),
                Fields\Text::make('Pad', fn () => "https://{$this->site->domain}/{$this->slug}"),
            ]),

            Fields\Stack::make('Bewerkt door', [
                Fields\Text::make('Bewerkt door', fn () => $this->updated_by?->name ?? null),
                Fields\DateTime::make('Bewerkt op', 'created_at'),
            ]),

            Fields\Boolean::make('Zichtbaar', 'visible'),
        ];
    }

    /**
     * Super short list when creating a new page.
     */
    public function fieldsForCreate(Request $request): array
    {
        return [
            Fields\BelongsTo::make('Site', 'site')
                ->rules([
                    'required',
                ]),

            Fields\Text::make('Titel', 'title')
                ->rules([
                    'required',
                    'min:3',
                    'max:255',
                ]),
        ];
    }

    /**
     * Regular fields for details and edit.
     * @return array
     */
    public function fields(Request $request)
    {
        $notWhenRequired = fn () => $this->type !== PageType::Required;

        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make('Site', 'site')
                ->resolveUsing(fn ($value) => $value->domain)
                ->onlyOnForms()
                ->readonly(),

            Fields\Text::make('Titel', 'title')
                ->rules([
                    'required',
                    'min:3',
                    'max:255',
                ]),

            Fields\Slug::make('Pad', 'slug')
                ->from('title')
                ->showOnUpdating($notWhenRequired)
                ->rules(fn () => [
                    'required',
                    'min:3',
                    'max:255',
                    Rule::unique('minisite_pages', 'slug')->where('site_id', $this->site->id)->ignore($request->resourceId),
                ]),

            Fields\Boolean::make('Zichtbaar', 'visible')
                ->placeholder('Een onzichtbare pagina is te bezoeken, maar verschijnt niet in de sitemap.')
                ->showOnUpdating($notWhenRequired)
                ->filterable(),

            Fields\Image::make('Afbeelding', 'cover')
                ->disk(Config::get('gumbo.images.disk'))
                ->path(path_join(Config::get('gumbo.images.path'), 'pages'))
                ->thumbnail(fn () => (string) image_asset($this->cover)->preset('nova-thumbnail'))
                ->preview(fn () => (string) image_asset($this->cover)->preset('nova-preview'))
                ->deletable()
                ->nullable()
                ->acceptedTypes(['image/jpeg', 'image/png'])
                ->help('Afbeelding die bij de pagina en op Social Media getoond wordt. Maximaal 2MB')
                ->rules(
                    'nullable',
                    'image',
                    'mimes:jpeg,png',
                    'max:2048',
                ),

            // Add data
            EditorJs::make('Inhoud', 'contents')->hideFromIndex(),
        ];
    }

    public function filters(Request $filters)
    {
        return [
            MinisiteFilter::make(),
        ];
    }
}
