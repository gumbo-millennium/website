<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Enrollment;
use App\Nova\Metrics\Traits\HasOnlyHostedEnrollments;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class NewEnrollments extends Value
{
    use HasOnlyHostedEnrollments;

    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Nieuwe inschrijvingen';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count(
            $request,
            $this->getHostedEnrollmentsQuery($request),
            'updated_at',
        )->allowZeroResult();
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
        return now()->addMinutes(15);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'new-enrollments';
    }

    public function authorizedToSee(Request $request)
    {
        return $request->user()->can('viewAny', Enrollment::class) && parent::authorizedToSee($request);
    }
}
