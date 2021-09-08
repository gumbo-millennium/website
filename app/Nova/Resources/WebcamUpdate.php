<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\WebcamUpdate as Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;

class WebcamUpdate extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Model::class;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Bestuurszaken';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make(__('Name'), 'name'),

            Fields\Text::make(__('IP'), 'ip'),

            Fields\Text::make(__('User Agent'), 'user_agent')
                ->onlyOnDetail(),
        ];
    }
}
