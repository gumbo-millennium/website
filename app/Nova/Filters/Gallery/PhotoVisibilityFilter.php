<?php

declare(strict_types=1);

namespace App\Nova\Filters\Gallery;

use App\Enums\PhotoVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Filters\BooleanFilter;

class PhotoVisibilityFilter extends BooleanFilter
{
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Visibility';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        $visibilities = Collection::make([
            PhotoVisibility::Visible,
            $value['hidden'] ? PhotoVisibility::Hidden : null,
        ])->filter()->values();

        return $query
            ->where(fn ($query) => $query->whereIn('visibility', $visibilities));
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            __('Show hidden photos') => 'hidden',
        ];
    }

    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __($this->name);
    }
}
