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

            // Show timestamps
            DateTime::make('Created at', 'created_at')->onlyOnDetail(),
            DateTime::make('Updated at', 'created_at')->onlyOnDetail(),

            // Paired files
            HasMany::make('Files', 'files', File::class),

            new Panel('Statistics', [
                // Make extra data
                Number::make('File count', function () {
                    return $this->files()->count();
                })->exceptOnForms(),

                // List downloads, in time frames
                Number::make('File downloads (48hrs)', function () {
                    return $this->downloads()->where('file_downloads.created_at', '>', now()->subDays(2))->count();
                })->exceptOnForms(),
                Number::make('File downloads (1 week)', function () {
                    return $this->downloads()->where('file_downloads.created_at', '>', now()->subWeek())->count();
                })->onlyOnDetail(),
                Number::make('File downloads (all time)', function () {
                    return $this->downloads()->count();
                })->onlyOnDetail(),
            ])
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
