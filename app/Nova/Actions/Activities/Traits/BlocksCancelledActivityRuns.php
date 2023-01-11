<?php

declare(strict_types=1);

namespace App\Nova\Actions\Activities\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

trait BlocksCancelledActivityRuns
{
    /**
     * Determine if the action is executable for the given request.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    public function authorizedToRun(Request $request, $model)
    {
        if ($model->is_cancelled) {
            return false;
        }

        if ($model->end_date < Date::now() && ! $model->is_postponed) {
            return false;
        }

        return parent::authorizedToRun($request, $model);
    }
}
