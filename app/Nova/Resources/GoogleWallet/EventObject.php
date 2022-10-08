<?php

declare(strict_types=1);

namespace App\Nova\Resources\GoogleWallet;

use App\Enums\Models\GoogleWallet\ReviewStatus;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields;

class EventObject extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\GoogleWallet\EventObject';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'wallet_id';

    /**
     * Name of the group.
     *
     * @var string
     */
    public static $group = 'Ops';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'wallet_id',
    ];

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make('wallet_id')->sortable(),

            Fields\MorphTo::make('Subject', 'subject'),

            Fields\Status::make('Review Status', 'review_status')
                ->loadingWhen([ReviewStatus::Draft, ReviewStatus::UnderReview])
                ->failedWhen([ReviewStatus::Rejected])
                ->filterable(fn ($request, $query, $value, $attrbiute) => $query->whereIn('status', Arr::wrap($value))),

            Fields\Code::make('Review details', fn () => json_encode($this->review, JSON_PRETTY_PRINT)),

            Fields\Number::make('Active installations', 'installations'),
        ];
    }
}
