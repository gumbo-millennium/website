<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\FileBundle;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class DownloadsPerDay extends Trend
{
    /**
     * The displayable name of the metric.
     * @var string
     */
    public $name = 'Downloads';

    /**
     * Calculate the value of the metric.
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->countByDays($request, Model::class);
    }

    /**
     * Get the ranges available for the metric.
     * @return array
     */
    public function ranges()
    {
        return [
            14 => '14 dagen',
            30 => '30 dagen',
            60 => '60 dagen',
            90 => '90 dagen',
            'TODAY' => 'vandaag'
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     * @return string
     */
    public function uriKey()
    {
        return 'downloads-per-day';
    }

    /**
     * @inheritdoc
     */
    public function authorizedToSee(Request $request)
    {
        return $request->user()->can('viewAny', FileBundle::class) && parent::authorizedToSee($request);
    }
}
