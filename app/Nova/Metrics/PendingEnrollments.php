<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Created;
use App\Models\States\Enrollment\Seeded;
use App\Nova\Metrics\Traits\HasOnlyHostedEnrollments;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class PendingEnrollments extends Value
{
    use HasOnlyHostedEnrollments;

    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'Inschrijvingen in afwachting';

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count(
            $request,
            $this->getHostedEnrollmentsQuery($request)->whereState(
                'state',
                [Created::class, Seeded::class]
            ),
            'updated_at'
        );
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
        return 'pending-enrollments';
    }

    /**
     * @inheritdoc
     */
    public function authorizedToSee(Request $request)
    {
        return $request->user()->can('viewAny', Enrollment::class) && parent::authorizedToSee($request);
    }
}
