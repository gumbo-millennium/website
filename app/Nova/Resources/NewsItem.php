<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\NewsItem as NewsItemModel;
use App\Nova\Fields\EditorJs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields;

/**
 * News Items.
 */
class NewsItem extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = NewsItemModel::class;

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
        $options = config('gumbo.news-categories', ['Nieuws']);

        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make('Titel', 'title'),

            Fields\Slug::make('Pad', 'slug')
                ->from('title')
                ->nullable(false),

            // Category
            Fields\Select::make('Categorie', 'category')
                ->required()
                ->options(\array_combine($options, $options)),

            // Add sponsor field
            Fields\Text::make('Headline', 'headline')
                ->nullable()
                ->help('De headline van dit artikel, net zoals in de krant.')
                ->hideFromIndex(),

            // Add multi selects
            Fields\BelongsTo::make('Laatst bewerkt door', 'author', User::class)
                ->onlyOnDetail(),

            // Add sponsor field
            Fields\Text::make('Sponsor', 'sponsor')
                ->nullable()
                ->help('De sponsor die voor deze post heeft betaald. Zet dit artikel om in een advertorial.')
                ->hideFromIndex(),

            // Show timestamps
            Fields\DateTime::make('Aangemaakt op', 'created_at')->onlyOnDetail(),
            Fields\DateTime::make('Laatst bewerkt op', 'created_at')->onlyOnDetail(),
            Fields\DateTime::make('Gepubliceerd op', 'published_at')
                ->nullable()
                ->help('Datum waarop dit artikel gepubliceerd is of wordt')
                ->hideFromIndex(),

            // Image
            Fields\Image::make('Afbeelding', 'cover')
                ->disk(Config::get('gumbo.images.disk'))
                ->path(path_join(Config::get('gumbo.images.path'), 'news'))
                ->thumbnail(fn () => (string) image_asset($this->cover)->preset('nova-thumbnail'))
                ->preview(fn () => (string) image_asset($this->cover)->preset('nova-preview'))
                ->deletable()
                ->nullable()
                ->acceptedTypes(['image/jpeg', 'image/png'])
                ->help('Afbeelding die bij het artikel en op Social Media getoond wordt. Maximaal 2MB')
                ->rules(
                    'nullable',
                    'image',
                    'mimes:jpeg,png',
                    'max:2048',
                ),

            // Add data
            EditorJs::make('Inhoud', 'contents')->hideFromIndex()->stacked(),
        ];
    }
}
