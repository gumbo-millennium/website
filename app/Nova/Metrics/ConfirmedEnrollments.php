<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Nova\Metrics\Traits\HasOnlyHostedEnrollments;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class ConfirmedEnrollments extends Trend
{
    use HasOnlyHostedEnrollments;

    /**
     * The displayable name of the metric.
     * @var string
     */
    public $name = 'Succesvolle inschrijvingen';

    /**
     * Calculate the value of the metric.
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->countByDays(
            $request,
            $this->getHostedEnrollmentsQuery($request)->whereState(
                'state',
                [Paid::class, Confirmed::class]
            ),
            'updated_at'
        );
    }

    /**
     * Get the ranges available for the metric.
     * @return array
     */
    public function ranges()
    {
        return [
            7 => '7 dagen',
            14 => '14 dagen',
            30 => '30 dagen',
            'TODAY' => 'vandaag'
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(15);
    }

    /**
     * Get the URI key for the metric.
     * @return string
     */
    public function uriKey()
    {
        return 'confirmed-enrollments';
    }

    /**
     * @inheritdoc
     */
    public function authorizedToSee(Request $request)
    {
        return $request->user()->can('viewAny', Enrollment::class) && parent::authorizedToSee($request);
    }
}
