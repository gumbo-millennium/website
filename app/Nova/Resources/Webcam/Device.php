<?php

declare(strict_types=1);

namespace App\Nova\Resources\Webcam;

use App\Models\Webcam\Device as DeviceModel;
use App\Nova\Resources\Resource;
use App\Nova\Resources\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;

class Device extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = DeviceModel::class;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Apparaten';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'slug',
    ];

    public function title()
    {
        return __(':name on :device', [
            'name' => $this->name,
            'device' => $this->device,
        ]);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make(__('Device Name'), 'device')
                ->sortable()
                ->readonly(),

            Fields\Text::make(__('Name'), 'name')
                ->sortable()
                ->readonly(),

            Fields\BelongsTo::make(__('User'), 'owner', User::class)
                ->exceptOnForms(),

            Fields\BelongsTo::make(__('Camera'), 'camera')
                ->sortable()
                ->nullable(),

                Fields\Image::make(__('Most recent image'), 'path')
                    ->disk(Config::get('gumbo.images.disk'))
                    ->thumbnail(fn () => (string) image_asset($this->path)->preset('nova-thumbnail'))
                    ->preview(fn () => (string) image_asset($this->path)->preset('nova-preview'))
                    ->exceptOnForms(),
        ];
    }
}
