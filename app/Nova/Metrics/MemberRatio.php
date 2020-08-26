<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class MemberRatio extends Partition
{
    /**
     * The displayable name of the metric.
     * @var string
     */
    public $name = 'Leden naar groep';

    /**
     * Calculate the value of the metric.
     * @param Request $request
     * @return mixed
     */
    public function calculate(Request $request) // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        // Get all users and all members
        $allUsers = User::count();
        $memberUsers = User::query()->role('member')->count();

        // Return counts
        return $this->result([
            'Leden' => $memberUsers,
            'Bezoekers' => $allUsers - $memberUsers,
        ]);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // Cache long
        return now()->addHours(3);
    }

    /**
     * Get the URI key for the metric.
     * @return string
     */
    public function uriKey()
    {
        return 'member-ratio';
    }

    /**
     * @inheritdoc
     */
    public function authorizedToSee(Request $request)
    {
        return $request->user()->can('viewAny', User::class) && parent::authorizedToSee($request);
    }
}
