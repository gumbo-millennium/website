<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Models\Role;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Filters\Filter;

/**
 * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
 */
class UserRoleFilter extends Filter
{
    private const AVAILABLE_ROLES = [
        'verified',
        'member'
    ];

    /**
     * The displayable name of the filter.
     */
    public $name = 'Gebruikersgroep';

    /**
     * Apply the filter to the given query.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        // No filter or filter by all
        if (!$value || $value === 'all') {
            return $query;
        }

        // Filter by role
        return $query->role($value);
    }

    /**
     * Get the filter's available options.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $options = [
            'Alles' => 'all'
        ];
        return $options + Role::whereIn('name', self::AVAILABLE_ROLES)->pluck('name', 'title')->toArray();
    }
}
