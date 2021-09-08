<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\Webcam as Models;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;

class Webcam extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Models::class;

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
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'slug',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make(__('Name'), 'name')
                ->sortable()
                ->rules([
                    'required',
                    'max:200',
                ]),

            Fields\Text::make(__('Slug'), 'slug')
                ->hideWhenCreating()
                ->readonly(fn () => $this->exists),

            Fields\Text::make(__('Telegram command'), 'command')
                ->help(__('The bot command that will show the cam, must end with "cam".'))
                ->hideFromIndex()
                ->creationRules([
                    'required',
                    'max:30',
                    'unique:webcams,command',
                ])
                ->updateRules([
                    'required',
                    'max:30',
                    'unique:webcams,command,{{resourceId}}',
                ]),

            Fields\HasMany::make(__('Webcam Updates'), 'updates', WebcamUpdate::class),
        ];
    }
}
