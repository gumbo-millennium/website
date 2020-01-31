<?php

namespace App\Nova\Resources;

use App\Models\FileCategory as FileCategoryModel;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use DanielDeWit\NovaPaperclip\PaperclipFile;
use DanielDeWit\NovaPaperclip\PaperclipImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

class FileCategory extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = FileCategoryModel::class;

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
        return 'Bestandscategorieën';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Bestandscategorie';
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

            // Title and slug
            TextWithSlug::make('Titel', 'title')
                ->slug('slug')
                ->rules('required', 'min:4')
                ->help('File title, does not need to be a filename'),
            Slug::make('Pad', 'slug')
                ->nullable(false)
                ->creationRules('unique:activities,slug')
                ->updateRules('unique:activities,slug,{{resourceId}}'),

            // Show timestamps
            DateTime::make('Aangemaakt op', 'created_at')->onlyOnDetail(),
            DateTime::make('Laatst bewerkt op', 'created_at')->onlyOnDetail(),

            // Paired files
            HasMany::make('Bestanden', 'files', File::class),

            new Panel('Statistieken', [
                // Make extra data
                Number::make('Aantal bestanden', function () {
                    return $this->files()->count();
                })->exceptOnForms(),

                // List downloads, in time frames
                Number::make('Aantal downloads (48hrs)', function () {
                    return $this->downloads()->where('file_downloads.created_at', '>', now()->subDays(2))->count();
                })->exceptOnForms(),
                Number::make('Aantal downloads (1 week)', function () {
                    return $this->downloads()->where('file_downloads.created_at', '>', now()->subWeek())->count();
                })->onlyOnDetail(),
                Number::make('Aantal downloads (all time)', function () {
                    return $this->downloads()->count();
                })->onlyOnDetail(),
            ])
        ];
    }
}
