<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Models\Minisite\Site;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class MinisiteFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Site';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->when($value, fn ($query) => $query->where('site_id', $value));
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return Site::query()->pluck('id', 'name');
    }

    /**
     * Set the default options for the filter.
     *
     * @return array|mixed
     */
    public function default()
    {
        return null;
    }
}
