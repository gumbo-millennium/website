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
    public static $group = 'File storage';

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
            TextWithSlug::make('Title', 'title')
                ->slug('slug')
                ->rules('required', 'min:4')
                ->help('File title, does not need to be a filename'),
            Slug::make('Slug', 'slug')
                ->nullable(false)
                ->creationRules('unique:activities,slug')
                ->updateRules('unique:activities,slug,{{resourceId}}'),

            // Owning category
            BelongsTo::make('Category', 'category', FileCategory::class)
                ->help('Category the file belongs to')
                ->rules('required'),

            // Add multi selects
            BelongsTo::make('Uploaded by', 'owner', User::class)
                ->onlyOnDetail(),

            // Show timestamps
            DateTime::make('Created at', 'created_at')->onlyOnDetail(),
            DateTime::make('Updated at', 'created_at')->onlyOnDetail(),

            new Panel('File information', [
                // Paperclip file
                PaperclipFile::make('File', 'file')
                    ->mimes(['pdf'])
                    ->rules('required', 'mimes:pdf')
                    ->disableDownload()
                    ->deletable(false)
                    ->readonly(function () {
                        return $this->exists && $this->file;
                    })
                    ->help('File the users will download, immutable once set'),

                // Thumbnail
                PaperclipImage::make('Thumbnail', 'thumbnail')
                    ->onlyOnDetail(),
            ]),

            new Panel('File Settings', [
                Boolean::make('Pulled or superseded', 'pulled')
                    ->help('Indicates the file has been replaced by a different version, or is no longer applicable')
                    ->rules('required_with:replacement'),
                BelongsTo::make('Replacement file', 'replacement', File::class)
                    ->help('If pulled, indicates which file replaces it')
                    ->nullable(),
            ]),

            new Panel('File metadata', [
                // Make extra data
                Text::make('Contents', 'contents')
                    ->onlyOnDetail(),
                Number::make('Page count', 'pages')
                    ->onlyOnDetail(),
                Code::make('Metadata', 'file_meta')
                    ->json()
                    ->onlyOnDetail(),
            ])
        ];
    }
}
