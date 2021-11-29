<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Sponsor as SponsorModel;
use App\Nova\Fields\Logo;
use App\Nova\Metrics\SponsorClicksPerDay;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields;
use Laravel\Nova\Panel;

/**
 * Add sponsor.
 */
class Sponsor extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = SponsorModel::class;

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
    public static $group = 'Bestuurszaken';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'url',
    ];

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            // Name and slug
            TextWithSlug::make('Naam', 'name')->slug('slug'),
            Slug::make('Pad', 'slug')
                ->nullable(false)
                ->hideFromIndex()
                ->readonly(fn () => $this->exists())
                ->rules(['min:3', 'unique:sponsors,slug'])
                ->help('Kan niet worden aangepast na aanmaken'),

            // Counts
            Fields\Number::make('Aantal weergaven', 'view_count')
                ->onlyOnDetail(),
            Fields\Number::make('Totaal aantal kliks', 'click_count')
                ->onlyOnDetail(),
            Fields\Sparkline::make('Aantal kliks')
                ->data(new SponsorClicksPerDay(null, $this->model())),

            // Advert info
            new Panel('Contract', [
                Fields\DateTime::make('Weergeven vanaf', 'starts_at')
                    ->required(),
                Fields\DateTime::make('Weergeven tot', 'ends_at'),
            ]),

            new Panel('Advertentie', [
                Logo::make('Logo (kleur)', 'logo_color'),
                Logo::make('Logo (monochroom)', 'logo_gray')
                    ->hideFromIndex(),

                // URL
                Fields\Text::make('URL', 'url')
                    ->required()
                    ->rules('required', 'url')
                    ->hideFromIndex(),

                // Text and backdrop
                Fields\Heading::make('Site-brede advertentie'),
                Fields\Textarea::make('Advertentietekst', 'caption')
                    ->help('Tekst in de advertentie, maximaal 40 woorden.'),

                Fields\Image::make('Achtergrond', 'cover')
                    ->thumbnail(fn () => (string) image_asset($this->cover)->preset('nova-thumbnail'))
                    ->preview(fn () => (string) image_asset($this->cover)->preset('nova-preview'))
                    ->deletable()
                    ->nullable()
                    ->acceptedTypes(['image/jpeg', 'image/png'])
                    ->help('Afbeelding achter de banner. Verhouding 2:1, minimaal 640px breed, maximaal 2MB')
                    ->rules(
                        'nullable',
                        'image',
                        'mimes:jpeg,png',
                        'max:2048',
                        Rule::dimensions()->minWidth(640),
                    ),

                Fields\Heading::make('Pagina-advertentie'),
                // Add title
                Fields\Text::make('Titel', 'contents_title')
                    ->help('Titel van de detailpagina')
                    ->hideFromIndex(),

                // Add data
                NovaEditorJs::make('Detailpagina', 'contents')
                    ->hideFromIndex()
                    ->stacked()
                    ->help('Inhoud van detailpagina'),
            ]),
        ];
    }

    /**
     * @inheritdoc
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function cards(Request $request)
    {
        return [
            SponsorClicksPerDay::make(),
        ];
    }
}
