<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\User;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class NewUsers extends Value
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Nieuwe gebruikers';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(Request $request)
    {
        return $this->count($request, User::class);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            14 => '14 dagen',
            30 => '30 dagen',
            60 => '60 dagen',
            90 => '90 dagen',
            'TODAY' => 'vandaag',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return DateInterval|DateTimeInterface|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'new-users';
    }

    public function authorizedToSee(Request $request)
    {
        return $request->user()->can('viewAny', User::class) && parent::authorizedToSee($request);
    }
}
