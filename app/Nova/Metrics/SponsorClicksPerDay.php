<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Sponsor;
use App\Models\SponsorClick;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class SponsorClicksPerDay extends Trend
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Doorkliks naar sponsoren';

    private ?Sponsor $sponsor;

    public function __construct($component = null, ?Sponsor $sponsor = null)
    {
        parent::__construct($component);

        $this->sponsor = $sponsor;
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request)
    {
        $query = SponsorClick::query();
        if ($this->sponsor) {
            $query->whereSponsorId($this->sponsor->id);
        }

        return $this->sumByDays($request, $query, 'count', 'date');
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
        // return now()->addMinutes(15);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'sponsor-clicks-per-day';
    }

    /**
     * @inheritdoc
     */
    public function authorizedToSee(Request $request)
    {
        return $request->user()->can('viewAny', Sponsor::class) && parent::authorizedToSee($request);
    }
}
