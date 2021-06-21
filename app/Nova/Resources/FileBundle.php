<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Helpers\Str;
use App\Models\FileBundle as FileBundleModel;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Ebess\AdvancedNovaMediaLibrary\Fields\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Panel;

/**
 * File resource, highly linked.
 */
class FileBundle extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = FileBundleModel::class;

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
    public static $group = 'Documentensysteem';

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
        $maxSize = Cache::remember(
            'nova.filebundle.maxsize',
            now()->addDay(),
            static fn () => Str::filesize(config('medialibrary.max_file_size')),
        );

        return [
            ID::make()->sortable(),

            // Title and slug
            TextWithSlug::make('Titel', 'title')
                ->slug('slug')
                ->rules('required', 'min:4')
                ->help('Titel van de bundel'),
            Slug::make('Pad', 'slug')
                ->nullable(false)
                ->readonly(fn () => $this->exists)
                ->creationRules('unique:file_bundles,slug')
                ->updateRules('unique:file_bundles,slug,{{resourceId}}'),

            // Owning category
            BelongsTo::make('Categorie', 'category', FileCategory::class)
                ->help('Categorie waarin deze bundel thuis hoort')
                ->rules('required'),

            // Data
            Textarea::make('Omschrijving', 'description')
                ->nullable(),

            Select::make('Sorteren op', 'sort_order')
                ->options([
                    'asc' => 'Oplopend alfabetisch',
                    'desc' => 'Aflopend alfabetisch',
                ])
                ->help('Bij datumnotaties, gebruik ISO 8601 (jjjj-mm-dd) om de volgorde goed te houden.')
                ->displayUsingLabels()
                ->hideFromIndex(),

            // Show timestamps
            DateTime::make('Aangemaakt op', 'created_at')->onlyOnDetail(),
            DateTime::make('Laatst bewerkt op', 'updated_at')->onlyOnDetail(),
            DateTime::make('Publicatiedatum', 'published_at')
                ->help('Datum waarop deze bundel openbaar wordt of is geworden.')
                ->hideFromIndex(),

            // Files
            Files::make('Bestanden', 'default')
                ->help("Bestand dat de leden downloaden, max {$maxSize} per bestand, alleen PDF.")
                ->singleMediaRules('mimetypes:application/pdf'),

            // Read-only metadata
            new Panel('Metadata', [
                Text::make('Totale bestandsgrootte', fn () => Str::filesize($this->total_size))
                    ->onlyOnDetail(),
                Number::make('Aantal downloads', 'downloads_count')
                    ->onlyOnDetail(),
            ]),
        ];
    }
}
