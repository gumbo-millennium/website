<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Created;
use App\Models\States\Enrollment\Paid;
use App\Models\States\Enrollment\Refunded;
use App\Models\States\Enrollment\Seeded;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class EnrollmentStateFilter extends BooleanFilter
{
    /**
     * All available states.
     */
    private const VALID_STATES = [
        Created::class => Created::NAME,
        Seeded::class => Seeded::NAME,
        Confirmed::class => Confirmed::NAME,
        Paid::class => Paid::NAME,
        Cancelled::class => Cancelled::NAME,
        Refunded::class => Refunded::NAME,
    ];

    /**
     * All default states.
     */
    private const DEFAULT_STATES = [
        Created::NAME,
        Seeded::NAME,
        Confirmed::NAME,
        Paid::NAME,
    ];

    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Status';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        // Get all checked values that are actually states
        $states = collect($value)
            ->filter()
            ->keys()
            ->filter(static fn ($val) => \in_array($val, self::VALID_STATES, true))
            ->values()
            ->all();

        // Add to query and return
        return $query->whereState('state', $states);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        // Model for mocking
        $namingModel = new Enrollment();

        // Collect names
        return collect(self::VALID_STATES)
            ->mapWithKeys(static fn ($val, $className) => [(new $className($namingModel))->title => $val])
            ->toArray();
    }

    /**
     * Set the default options for the filter.
     *
     * @return array
     */
    public function default()
    {
        return collect(self::VALID_STATES)
            ->mapWithKeys(static fn ($state) => [$state => \in_array($state, self::DEFAULT_STATES, true)])
            ->all();
    }
}
