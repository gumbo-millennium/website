<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Sponsor as SponsorModel;
use App\Nova\Fields\Logo;
use App\Nova\Metrics\SponsorClicksPerDay;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use DanielDeWit\NovaPaperclip\PaperclipImage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Sparkline;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Panel;

/**
 * Add sponsor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sponsor extends Resource
{
    /**
     * The model the resource corresponds to.
     * @var string
     */
    public static $model = SponsorModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     * @var string
     */
    public static $title = 'name';

    /**
     * Name of the group
     * @var string
     */
    public static $group = 'Bestuurszaken';

    /**
     * The columns that should be searched.
     * @var array
     */
    public static $search = [
        'name',
        'url',
    ];

    /**
     * Get the displayable label of the resource.
     * @return string
     */
    public static function label()
    {
        return 'Sponsoren';
    }

    /**
     * Get the displayable singular label of the resource.
     * @return string
     */
    public static function singularLabel()
    {
        return 'Sponsor';
    }

    /**
     * Get the fields displayed by the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            // Name and slug
            TextWithSlug::make('Naam', 'name')->slug('slug'),
            Slug::make('Pad', 'slug')
                ->nullable(false)
                ->hideFromIndex()
                ->readonly(fn () => $this->exists())
                ->rules(['min:3', 'unique:sponsors,slug'])
                ->help('Kan niet worden aangepast na aanmaken'),


            // Counts
            Number::make('Aantal weergaven', 'view_count')
                ->onlyOnDetail(),
            Number::make('Totaal aantal kliks', 'click_count')
                ->onlyOnDetail(),
            Sparkline::make('Aantal kliks')
                ->data(new SponsorClicksPerDay($this->id)),

            // Advert info
            new Panel('Contract', [
                DateTime::make("Weergeven vanaf", 'starts_at')
                    ->required(),
                DateTime::make('Weergeven tot', 'ends_at'),
                ]),

            new Panel('Advertentie', [
                Logo::make('Logo (kleur)', 'logo_color'),
                Logo::make('Logo (monochroom)', 'logo_gray')
                    ->hideFromIndex(),

                // URL
                Text::make('URL', 'url')
                    ->required()
                    ->rules('required', 'url')
                    ->hideFromIndex(),

                // Text and backdrop
                Heading::make('Site-brede advertentie'),
                Textarea::make('Advertentietekst', 'caption')
                    ->help('Tekst in de advertentie, maximaal 40 woorden.'),
                PaperclipImage::make('Achtergrond', 'backdrop')
                    ->deletable()
                    ->mimes(['png', 'jpeg', 'jpg'])
                    ->help('Afbeelding achter de banner, verhouding 2:1, minimaal 640px breed')
                    ->minWidth(640)
                    ->minHeight(320)
                    ->hideFromIndex()
                    ->rules(
                        'nullable',
                        'image',
                        'mimes:jpeg,png',
                        'max:2048',
                        Rule::dimensions()->ratio(2 / 1)
                    ),

                Heading::make('Pagina-advertentie'),
                // Add title
                Text::make('Titel', 'contents_title')
                    ->help('Titel van de detailpagina')
                    ->hideFromIndex(),

                // Add data
                NovaEditorJs::make('Detailpagina', 'contents')
                    ->hideFromIndex()
                    ->stacked()
                    ->help('Inhoud van detailpagina'),
                ])
        ];
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function cards(Request $request)
    {
        return [
            SponsorClicksPerDay::make()
        ];
    }
}
