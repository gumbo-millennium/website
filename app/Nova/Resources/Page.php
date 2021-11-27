<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Helpers\Str;
use App\Models\Page as PageModel;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields;

/**
 * Add page.
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
     * Name of the group.
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

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            TextWithSlug::make('Titel', 'title')->slug('slug'),
            Slug::make('Deelpad', 'slug')
                ->nullable(false)
                ->hideFromIndex()
                ->readonly(fn () => array_key_exists($this->slug, PageModel::REQUIRED_PAGES))
                ->rules([
                    Rule::unique('pages', 'slug')->where(function ($query) {
                        if ($this->id) {
                            $query = $query->where('id', '!=', $this->id);
                        }
                        if ($this->group === null) {
                            return $query->whereNull('group');
                        }

                        return $query->where('group', $this->group);
                    }),
                ]),

            // Group
            Fields\Select::make('Groep', 'group')
                ->nullable()
                ->hideFromIndex()
                ->readonly(fn () => $this->exists)
                ->options(config('gumbo.page-groups')),

            // Group / Slug, for index
            Fields\Text::make('Pad', fn () => $this->group ? "{$this->group}/{$this->slug}" : $this->slug)
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            // Add multi selects
            Fields\BelongsTo::make('Laatst bewerkt door', 'author', User::class)
                ->onlyOnDetail(),

            // Show timestamps
            Fields\DateTime::make('Aangemaakt op', 'created_at')->onlyOnDetail(),
            Fields\DateTime::make('Laatst bewerkt op', 'created_at')->onlyOnDetail(),

            // Hidden flag
            Fields\Boolean::make('Verbergen', 'hidden'),

            // Group / Slug, for index
            Fields\Text::make('Samenvatting', 'summary')
                ->nullable()
                ->rules('nullable', 'string', 'min:5', 'max:90')
                ->hideFromIndex()
                ->help('Wordt getoond op kaarten en in Google / Facebook'),

            Fields\Image::make('Afbeelding', 'cover')
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

            // Add type
            Fields\Text::make('Type')->onlyOnDetail()->displayUsing(static fn ($value) => Str::title($value)),

            // Add data
            NovaEditorJs::make('Inhoud', 'contents')->hideFromIndex()->stacked(),
        ];
    }
}
