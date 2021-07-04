<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\MemberReferral as MemberReferralModels;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class MemberReferral extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = MemberReferralModels::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'subject',
        'referred_by',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make(__('New member'), 'subject')
                ->readonly(fn () => $this->exists),

            Text::make(__('Referred by'), 'referred_by')
                ->readonly(fn () => $this->exists),

            BelongsTo::make(__('Referred by user'), 'user', User::class)
                ->nullable()
                ->searchable()
                ->withoutTrashed(),
        ];
    }
}
