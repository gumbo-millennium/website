<?php

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Page as PageModel;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

/**
 * Add page
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Page extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PageModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * Name of the group
     *
     * @var string
     */
    public static $group = 'Content';

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
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Pagina\'s';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Pagina';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            TextWithSlug::make('Titel', 'title')->slug('slug'),
            Slug::make('Pad', 'slug')
                ->nullable(false)
                ->readonly(function () {
                    return array_key_exists($this->slug, PageModel::REQUIRED_PAGES);
                }),

            // Add multi selects
            BelongsTo::make('Laatst bewerkt door', 'author', User::class)
                ->onlyOnDetail(),

            // Show timestamps
            DateTime::make('Aangemaakt op', 'created_at')->onlyOnDetail(),
            DateTime::make('Laatst bewerkt op', 'created_at')->onlyOnDetail(),

            // Add type
            Text::make('Type')->onlyOnDetail()->displayUsing(fn($value) => Str::title($value)),

            // Add data
            NovaEditorJs::make('Inhoud', 'contents')->hideFromIndex()->stacked(),
        ];
    }
}
