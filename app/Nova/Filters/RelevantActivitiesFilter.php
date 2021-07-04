<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class RelevantActivitiesFilter extends BooleanFilter
{
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
        // Future or past
        $query = $query->where('end_date', $value['show_past'] ? '<' : '>=', now());

        // Cancelled or not
        $method = $value['show_cancelled'] ? 'whereNotNull' : 'whereNull';
        $query = $query->{$method}('cancelled_at');

        // Return
        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Afgelopen' => 'show_past',
            'Geannuleerd' => 'show_cancelled',
        ];
    }
}
