<?php

declare(strict_types=1);

namespace App\Nova\Resources\Webcam;

use App\Models\Webcam\Camera as CameraModel;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields;

class Camera extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = CameraModel::class;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Apparaten';

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

            Fields\Slug::make(__('Slug'), 'slug')
                ->from('name')
                ->hideWhenCreating(),

            Fields\Text::make(__('Telegram command'), 'command')
                ->help(__('The bot command that will show the cam, must end with "cam".'))
                ->hideFromIndex()
                ->creationRules([
                    'required',
                    'max:30',
                    'unique:webcam_cameras,command',
                ])
                ->updateRules([
                    'required',
                    'max:30',
                    'unique:webcam_cameras,command,{{resourceId}}',
                ]),

            Fields\Text::make(__('Webcam Device'), function () {
                return ($this->device)
                    ? (new Device($this->device))->title()
                    : null;
            }),

            Fields\Image::make(__('Most recent image'), 'device.path')
                ->disk(Config::get('gumbo.images.disk'))
                ->thumbnail(fn () => (string) image_asset($this->device?->path)->preset('nova-thumbnail'))
                ->preview(fn () => (string) image_asset($this->device?->path)->preset('nova-preview'))
                ->exceptOnForms(),
        ];
    }
}
