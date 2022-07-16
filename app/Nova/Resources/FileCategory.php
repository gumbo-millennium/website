<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\FileCategory as FileCategoryModel;
use App\Nova\Metrics\DownloadsPerDay;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;
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
        return [
            Fields\ID::make()->sortable(),

            // Title and slug
            Fields\Text::make('Titel', 'title')
                ->rules('required', 'min:4')
                ->help('File title, does not need to be a filename'),

            Fields\Slug::make('Pad', 'slug')
                ->nullable(false)
                ->from('title')
                ->readonly(fn () => $this->exists)
                ->creationRules('unique:file_categories,slug')
                ->updateRules('unique:file_categories,slug,{{resourceId}}'),

            // Show timestamps
            Fields\DateTime::make('Aangemaakt op', 'created_at')->onlyOnDetail(),
            Fields\DateTime::make('Laatst bewerkt op', 'created_at')->onlyOnDetail(),

            // Paired files
            Fields\HasMany::make('Bundels', 'bundles', FileBundle::class),

            new Panel('Statistieken', [
                // List downloads, in time frames
                Fields\Number::make('Aantal downloads (48hrs)', fn () => $this->downloads()->where('file_downloads.created_at', '>', now()->subDays(2))->count())->exceptOnForms(),
                Fields\Number::make('Aantal downloads (1 week)', fn () => $this->downloads()->where('file_downloads.created_at', '>', now()->subWeek())->count())->onlyOnDetail(),
                Fields\Number::make('Aantal downloads (all time)', fn () => $this->downloads()->count())->onlyOnDetail(),
            ]),
        ];
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function cards(Request $request)
    {
        return [
            new DownloadsPerDay(),
        ];
    }
}
