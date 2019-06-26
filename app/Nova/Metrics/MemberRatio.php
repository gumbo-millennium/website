<?php

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class MemberRatio extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
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
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // Cache long
        return now()->addHours(3);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'member-ratio';
    }
}
