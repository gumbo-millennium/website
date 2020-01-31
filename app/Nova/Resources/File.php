<?php

namespace App\Nova\Resources;

use App\Models\File as FileModel;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use DanielDeWit\NovaPaperclip\PaperclipFile;
use DanielDeWit\NovaPaperclip\PaperclipImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * File resource, highly linked.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class File extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = FileModel::class;

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

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Bestanden';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Bestand';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            // Title and slug
            TextWithSlug::make('Titel', 'title')
                ->slug('slug')
                ->rules('required', 'min:4')
                ->help('Bestandstitel, hoeft geen bestandsnaam te zijn'),
            Slug::make('Pad', 'slug')
                ->nullable(false)
                ->creationRules('unique:activities,slug')
                ->updateRules('unique:activities,slug,{{resourceId}}'),

            // Owning category
            BelongsTo::make('Categorie', 'category', FileCategory::class)
                ->help('Categorie waarin dit bestand thuis hoort')
                ->rules('required'),

            // Add multi selects
            BelongsTo::make('Geupload door', 'owner', User::class)
                ->onlyOnDetail(),

            // Show timestamps
            DateTime::make('Aangemaakt op', 'created_at')->onlyOnDetail(),
            DateTime::make('Laatst bewerkt op', 'updated_at')->onlyOnDetail(),

            new Panel('Bestandsinformatie', [
                // Paperclip file
                PaperclipFile::make('Bestand', 'file')
                    ->mimes(['pdf'])
                    ->rules('required', 'mimes:pdf')
                    ->disableDownload()
                    ->deletable(false)
                    ->readonly(function () {
                        return $this->exists && $this->file;
                    })
                    ->help('Bestand dat de leden downloaden'),

                // Thumbnail
                PaperclipImage::make('Thumbnail', 'thumbnail')
                    ->onlyOnDetail(),
            ]),

            new Panel('Instellingen', [
                Boolean::make('Ingetrokken', 'pulled')
                    ->help('Markeer dit bestand als niet meer relevant. Leden kunnen het nog wel downloaden.')
                    ->rules('required_with:replacement'),
                BelongsTo::make('Vervanging', 'replacement', File::class)
                    ->help('Indien ingetrokken, vervangt dit bestand het ingetrokken bestand (een nieuwe versie oid)')
                    ->nullable(),
            ]),

            new Panel('Metadata', [
                // Make extra data
                Text::make('Inhoud', 'contents')
                    ->onlyOnDetail(),
                Number::make('Aantal pagina\'s', 'pages')
                    ->onlyOnDetail(),
                Code::make('Overige metadata', 'file_meta')
                    ->json()
                    ->onlyOnDetail(),
            ])
        ];
    }
}
