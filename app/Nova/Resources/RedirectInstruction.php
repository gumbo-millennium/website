<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\RedirectInstruction as RedirectInstructionModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\Text;

/**
 * Roles, for the Permission Framework.
 */
class RedirectInstruction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = RedirectInstructionModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'slug';

    /**
     * Name of the group.
     *
     * @var string
     */
    public static $group = 'Bestuurszaken';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'slug',
    ];

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
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make(__('Path'), 'slug')
                ->rules([
                    'required',
                    'max:255',
                ])
                ->creationRules('unique:redirect_instructions')
                ->updateRules('unique:redirect_instructions,slug,{{resourceId}}'),

            Text::make(__('Target path'), 'path')
                ->rules([
                    'required',
                    'max:255',
                ]),
        ];
    }
}
